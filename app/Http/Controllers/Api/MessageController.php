<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Donor;
use App\Traits\ResolvesDonorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    use ResolvesDonorSession;

    /**
     * Send a message to another donor
     * POST /api/messages
     */
    public function send(Request $request)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:donors,id|different:' . $donor->id,
            'content' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $donor->id,
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'message' => $request->content, // Map 'content' to 'message' column
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $this->formatMessage($message)
        ], 201);
    }

    /**
     * Fetch received messages
     * GET /api/messages/received
     */
    public function getReceivedMessages(Request $request)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $messages = Message::where('receiver_id', $donor->id)
            ->with(['sender:id,name,surname,profile_image'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages->map(fn($m) => $this->formatMessage($m))
        ]);
    }

    /**
     * Fetch sent messages
     * GET /api/messages/sent
     */
    public function getSentMessages(Request $request)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $messages = Message::where('sender_id', $donor->id)
            ->with(['receiver:id,name,surname,profile_image'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages->map(fn($m) => $this->formatMessage($m))
        ]);
    }

    /**
     * Mark a message as read
     * PUT /api/messages/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $message = Message::where('id', $id)
            ->where('receiver_id', $donor->id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get unread count
     * GET /api/messages/unread-count
     */
    public function getUnreadCount(Request $request)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $count = Message::where('receiver_id', $donor->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Helper to format message for API response
     */
    private function formatMessage($message)
    {
        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'content' => $message->message, // Map 'message' column to 'content'
            'subject' => $message->subject,
            'is_read' => (bool)$message->is_read,
            'created_at' => $message->created_at,
            'sender' => $message->sender,
            'receiver' => $message->receiver,
        ];
    }

    /**
     * Get conversation between two donors (Legacy/Internal use)
     */
    public function getConversation(Request $request)
    {
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $otherId = $request->query('other_id');
        if (!$otherId) {
            return response()->json(['success' => false, 'message' => 'other_id is required'], 400);
        }

        $messages = Message::conversation($donor->id, $otherId)
            ->with(['sender:id,name,surname,profile_image', 'receiver:id,name,surname,profile_image'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        Message::where('receiver_id', $donor->id)
            ->where('sender_id', $otherId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages->map(fn($m) => $this->formatMessage($m))
        ]);
    }
}
