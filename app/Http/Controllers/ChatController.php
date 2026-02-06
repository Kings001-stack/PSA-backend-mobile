<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Inventory;
use App\Models\Medication;
use App\Services\GeminiService;
use App\Services\SafetyEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function __construct(
        protected SafetyEngine $safetyEngine,
        protected GeminiService $geminiService
    ) {
    }

    /**
     * Display the chat page.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get existing conversations for the user
        $conversations = Conversation::where('tenant_id', $user->tenant_id)
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Send a message and get AI response.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $message = $request->input('message');
        $sessionId = $request->input('session_id') ?? (string) Str::uuid();

        // Step 1: Check safety engine
        $safetyCheck = $this->safetyEngine->check($message);

        if ($safetyCheck) {
            return $this->handleEscalation($user, $sessionId, $message, $safetyCheck);
        }

        // Step 2: Get or create conversation
        $conversation = $this->getOrCreateConversation($user->tenant_id, $sessionId, $user->name);

        // Step 3: Store user message and update title if empty
        $this->storeMessage($conversation->id, 'user', $message);
        
        if (empty($conversation->title)) {
            try {
                $title = $this->generateTitle($message);
                $conversation->update(['title' => $title]);
            } catch (\Exception $e) {
                // Fallback to truncation if AI title generation fails
                $conversation->update(['title' => Str::limit($message, 40)]);
            }
        }

        // Step 4: Generate AI response
        try {
            $prompt = $this->buildPrompt($message, $conversation->id);
            $response = $this->geminiService->generateResponse($prompt);
        } catch (\Exception $e) {
            \Log::error('Chat AI Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'message' => $message,
                'exception' => $e->getTraceAsString(),
            ]);
            
            // Provide a helpful fallback response
            $response = "I'm currently unable to process your request. This could be due to a configuration issue with our AI service. Please contact the pharmacy directly for assistance, or try again later.";
        }

        // Step 5: Store assistant message
        $this->storeMessage($conversation->id, 'assistant', $response);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'message' => $response,
            'escalation' => false,
        ]);
    }

    /**
     * Get conversation messages.
     */
    public function messages(Request $request)
    {
        $sessionId = $request->input('session_id');
        $user = auth()->user();

        if (!$sessionId) {
            return response()->json(['messages' => []]);
        }

        $conversation = Conversation::where('tenant_id', $user->tenant_id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$conversation) {
            return response()->json(['messages' => []]);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toISOString(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Handle escalation scenario.
     */
    protected function handleEscalation($user, string $sessionId, string $message, array $safetyCheck)
    {
        $conversation = $this->getOrCreateConversation($user->tenant_id, $sessionId, $user->name);

        $conversation->update([
            'requires_escalation' => true,
            'escalation_reason' => $safetyCheck['reason']->value,
            'escalated_at' => now(),
            'status' => 'escalated',
        ]);

        $this->storeMessage($conversation->id, 'user', $message);
        $this->storeMessage($conversation->id, 'system', $safetyCheck['message']);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'escalation' => true,
            'message' => $safetyCheck['message'],
            'reason' => $safetyCheck['reason']->value,
        ]);
    }

    /**
     * Get or create conversation.
     */
    protected function getOrCreateConversation(?int $tenantId, string $sessionId, ?string $customerName): Conversation
    {
        return Conversation::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'session_id' => $sessionId,
            ],
            [
                'customer_name' => $customerName,
                'status' => 'active',
            ]
        );
    }

    /**
     * Store a message.
     */
    protected function storeMessage(int $conversationId, string $role, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
        ]);
    }

    /**
     * Build the prompt for AI.
     */
    protected function buildPrompt(string $userMessage, int $conversationId): string
    {
        // Get conversation history
        $history = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->map(fn ($msg) => "{$msg->role}: {$msg->content}")
            ->implode("\n");

        // Get Real-time Inventory Data
        $inventoryData = Inventory::with('medication')
            ->get()
            ->map(function ($item) {
                $name = $item->medication ? $item->medication->name : 'Unknown';
                $qty = $item->quantity;
                return "- {$name}: " . ($qty > 0 ? "{$qty} units in stock" : "OUT OF STOCK");
            })
            ->unique()
            ->implode("\n");

        if (empty($inventoryData)) {
            $inventoryData = "No items registered in inventory yet.";
        }

        return <<<PROMPT
You are the AI assistant for **MediCare Demo Pharmacy**, a development/demo pharmacy. Here is the pharmacy information:

## PHARMACY INFORMATION

**Name:** MediCare Demo Pharmacy
**Location:** 456 Demo Street, Test City, CA 90210
**Phone:** (555) 000-1234
**Email:** demo@medicare-pharmacy.test

**Hours of Operation:**
- Monday - Friday: 8:00 AM - 9:00 PM
- Saturday: 9:00 AM - 6:00 PM  
- Sunday: 10:00 AM - 4:00 PM
- Holidays: Closed on major holidays

**Services Offered:**
- Prescription filling and refills
- Over-the-counter medications
- Immunizations (flu shots, COVID-19, shingles, pneumonia)
- Blood pressure & health screenings
- Diabetes care and supplies
- Medication therapy management
- Free home delivery for orders over \$30
- Drive-thru pickup
- Medication compounding services
- Pet prescriptions

**Insurance & Payment:**
- Most major insurance plans accepted
- Medicare Part D & Medicaid accepted
- Cash, Visa, Mastercard, AMEX, Discover
- 15% discount for uninsured patients
- Price matching available

**Policies:**
- Refills ready in 20-30 minutes
- Auto-refill program available
- Valid ID required for controlled substances
- Free medication disposal program
- 90-day supplies available for maintenance medications

## REAL-TIME INVENTORY STATUS
Use this data to answer questions about drug availability:
{$inventoryData}

## YOUR ROLE

You can help with:
- **Drug Availability**: If a user asks for a drug, check the REAL-TIME INVENTORY section above. 
- **Out of Stock**: If the drug is listed as "OUT OF STOCK" or not listed at all, clearly state that it is currently unavailable.
- General medication information (not medical advice).
- Pharmacy hours, location, and services.
- Prescription refill inquiries.
- General health & wellness tips.

## SAFETY RULES (MUST FOLLOW)

- NEVER provide dosage recommendations.
- NEVER diagnose medical conditions.
- NEVER suggest stopping or changing medications.
- For medical advice, always recommend consulting the pharmacist or doctor.
- For controlled substance questions, redirect to speak with the pharmacist in person.
- For emergencies, direct to call 911 immediately.

## CONVERSATION HISTORY
{$history}

## USER QUESTION
User: {$userMessage}

Respond in a helpful, friendly, and professional manner. Keep answers concise. If unsure about specific pharmacy details, suggest calling (555) 000-1234.
PROMPT;
    }

    /**
     * Generate a short title for the conversation.
     */
    protected function generateTitle(string $message): string
    {
        $prompt = <<<PROMPT
Generate a very short, specific title (3-5 words max) for a chat conversation that starts with this user message:
"{$message}"

Rules:
- Do not use quotes.
- Do not say "Title:" or "Subject:".
- Be concise and descriptive (e.g., "Flu Shot Information", "Refill for Lisinopril", "Pharmacy Hours").
PROMPT;

        return trim($this->geminiService->generateResponse($prompt));
    }
}
