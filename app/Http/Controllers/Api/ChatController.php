<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AIProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Inventory;
use App\Services\SafetyEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        protected SafetyEngine $safetyEngine,
        protected AIProviderInterface $aiProvider
    ) {
    }

    /**
     * Get chat history.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        $conversations = Conversation::where('tenant_id', $user->tenant_id)
            ->latest()
            ->limit(20)
            ->get();
            
        return response()->json($conversations);
    }

    /**
     * Send message.
     */
    public function send(Request $request)
    {
        \Log::info('Chat Send Request Received', $request->all());

        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $message = $request->input('message');
        $sessionId = $request->input('session_id') ?? (string) Str::uuid();

        \Log::info('Chat Details', ['user_id' => $user->id, 'tenant_id' => $user->tenant_id, 'session_id' => $sessionId]);

        // 1. Safety Check
        try {
            $safetyCheck = $this->safetyEngine->check($message);
            if ($safetyCheck) {
                \Log::info('Chat Safety Check Triggered', $safetyCheck);
                return $this->handleEscalation($user, $sessionId, $message, $safetyCheck);
            }
        } catch (\Exception $e) {
            \Log::error('Chat Safety Check Error: ' . $e->getMessage());
        }

        // 2. Conversation
        try {
            $conversation = $this->getOrCreateConversation($user->tenant_id, $sessionId, $user->name);
        } catch (\Exception $e) {
            \Log::error('Chat Conversation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Database error: could not create/find conversation.'], 500);
        }

        // 3. User Message
        $this->storeMessage($conversation->id, 'user', $message);
        
        if (empty($conversation->title)) {
            try {
                $title = $this->generateTitle($message);
                $conversation->update(['title' => $title]);
            } catch (\Exception $e) {
                \Log::warning('Chat Title Generation Failed: ' . $e->getMessage());
                $conversation->update(['title' => Str::limit($message, 40)]);
            }
        }

        // 4. AI Response
        try {
            $prompt = $this->buildPrompt($message, $conversation->id);
            \Log::info('Chat AI Prompt Built');
            $response = $this->aiProvider->generateResponse($prompt);
        } catch (\Exception $e) {
            \Log::error('Chat AI Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => "I'm having trouble connecting to my brain right now. Please try again later."], 500);
        }

        // 5. Store Response
        $this->storeMessage($conversation->id, 'assistant', $response);

        return response()->json([
            'session_id' => $sessionId,
            'message' => $response,
            'title' => $conversation->title,
        ]);
    }

    public function contact(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return response()->json([
            'phone' => '09071906688',
            'email' => 'e93521365@gmail.com',
            'pharmacy_name' => $tenant->name ?? 'PrimeChem Pharmacy',
        ]);
    }

    protected function handleEscalation($user, $sessionId, $message, $safetyCheck)
    {
        $conversation = $this->getOrCreateConversation($user->tenant_id, $sessionId, $user->name);
        $conversation->update([
            'requires_escalation' => true,
            'escalation_reason' => $safetyCheck['reason']->value,
            'status' => 'escalated',
        ]);

        $this->storeMessage($conversation->id, 'user', $message);
        $this->storeMessage($conversation->id, 'system', $safetyCheck['message']);

        // Load tenant for phone number
        $tenant = $user->tenant;

        return response()->json([
            'session_id' => $sessionId,
            'message' => $safetyCheck['message'],
            'escalate' => true,
            'reason' => $safetyCheck['reason']->value,
            'phone' => '09071906688',
        ]);
    }

    protected function getOrCreateConversation($tenantId, $sessionId, $customerName)
    {
        return Conversation::withoutGlobalScope('tenant')->firstOrCreate(
            ['tenant_id' => $tenantId, 'session_id' => $sessionId],
            ['customer_name' => $customerName, 'status' => 'active']
        );
    }

    protected function storeMessage($conversationId, $role, $content)
    {
        return Message::create([
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
        ]);
    }

    protected function buildPrompt($userMessage, $conversationId)
    {
        $history = Message::where('conversation_id', $conversationId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(fn ($msg) => $msg->role === 'user' ? "User: {$msg->content}" : "Assistant: {$msg->content}")
            ->implode("\n");

        $inventoryData = Inventory::with('medication')
            ->get()
            ->map(function ($item) {
                $name = $item->medication ? $item->medication->name : 'Unknown';
                $qty = $item->quantity;
                // Hide exact quantities and prices - only show availability status
                $status = $qty > 0 ? "In Stock" : "Out of Stock";
                return "- {$name}: {$status}";
            })
            ->unique()
            ->implode("\n");

        return <<<PROMPT
You are **CareBot**, the professional and friendly AI assistant for **PrimeChem Pharmacy**.

PHARMACY CONTEXT:
- **Location:** Karu Site Cornershop, Abuja FCT, Nigeria.
- **Phone:** 09071906688
- **Email:** e93521365@gmail.com
- **Hours (Mon-Fri):** 8:00 AM - 9:00 PM
- **Hours (Sat-Sun):** 10:00 AM - 6:00 PM
- **Services:** Prescription refills, health consultations, over-the-counter sales.

CURRENT INVENTORY & PRICES:
{$inventoryData}

GUIDELINES:
1. **Focus:** Help with medicine availability, prices, and pharmacy logistics.
2. **Stock Information:** You can tell customers if a medication is "available" or "in stock", but NEVER reveal specific quantities or stock numbers. Simply say "Yes, we have [medication name] in stock" or "Unfortunately, [medication name] is currently out of stock."
3. **Medical Safety:** If asked for medical advice or dosages, politely explain that you are an AI and they should consult our pharmacist or a doctor.
4. **Emergencies & Contact:** If a user mentions severe symptoms, or asks for our phone number/how to reach us, immediately provide the hotline **09071906688** and location (Karu Site).
5. **Tone:** Professional, empathetic, and concise.

HISTORY:
{$history}

User: {$userMessage}
Assistant:
PROMPT;
    }

    protected function generateTitle($message)
    {
        // Simple prompt for title
        return trim($this->aiProvider->generateResponse("Generate a 3-5 word title for: \"{$message}\""));
    }
}
