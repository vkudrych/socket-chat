<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(private readonly MessageService $messageService) {}

    public function index(): View
    {
        $users = User::where('id', '!=', auth()->id())
            ->withCount(['sentMessages as unread_count' => function ($q) {
                $q->where('receiver_id', auth()->id())->whereNull('read_at');
            }])
            ->get();

        $firstUser = $users->first();

        $messages = $firstUser
            ? $this->messageService->getConversation(auth()->user(), $firstUser)
            : collect();

        return view('chats', compact('users', 'messages', 'firstUser'));
    }

    public function conversation(User $user): JsonResponse
    {
        $this->messageService->markAsRead($user, auth()->user());
        $messages = $this->messageService->getConversation(auth()->user(), $user);

        return response()->json($messages);
    }
}