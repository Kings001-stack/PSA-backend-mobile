<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::where('tenant_id', $user->tenant_id)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('escalated'), function ($query) {
                $query->where('requires_escalation', true);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Get a specific conversation with all messages.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::where('tenant_id', $user->tenant_id)
            ->with('messages')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Assign a conversation to a user.
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $conversation = Conversation::where('tenant_id', $user->tenant_id)
            ->findOrFail($id);

        $conversation->update([
            'assigned_to' => $request->input('user_id'),
        ]);

        $this->logAction($user->id, $user->tenant_id, 'conversation_assigned', $conversation);

        return response()->json([
            'success' => true,
            'conversation' => $conversation->fresh(),
        ]);
    }

    /**
     * Respond to a conversation.
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::where('tenant_id', $user->tenant_id)
            ->findOrFail($id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $request->input('message'),
            'metadata' => [
                'responded_by' => $user->id,
                'responded_by_name' => $user->name,
            ],
        ]);

        $conversation->update([
            'status' => 'resolved',
            'requires_escalation' => false,
        ]);

        $this->logAction($user->id, $user->tenant_id, 'conversation_responded', $conversation);

        return response()->json([
            'success' => true,
            'message' => $message,
            'conversation' => $conversation->fresh(),
        ]);
    }

    /**
     * Mark conversation as resolved.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::where('tenant_id', $user->tenant_id)
            ->findOrFail($id);

        $conversation->update([
            'status' => 'resolved',
            'requires_escalation' => false,
        ]);

        $this->logAction($user->id, $user->tenant_id, 'conversation_resolved', $conversation);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Log admin action.
     */
    protected function logAction(int $userId, int $tenantId, string $action, Conversation $conversation): void
    {
        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'model_type' => Conversation::class,
            'model_id' => $conversation->id,
            'new_values' => $conversation->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
