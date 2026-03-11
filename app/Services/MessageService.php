<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    public function send(int $senderId, int $receiverId, string $message): Message
    {
        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    public function getConversation(User $authUser, User $withUser): Collection
    {
        return Message::where(function ($q) use ($authUser, $withUser) {
            $q->where('sender_id', $authUser->id)->where('receiver_id', $withUser->id);
        })->orWhere(function ($q) use ($authUser, $withUser) {
            $q->where('sender_id', $withUser->id)->where('receiver_id', $authUser->id);
        })->orderBy('created_at')->get();
    }

    public function markAsRead(User $sender, User $receiver): void
    {
        Message::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        broadcast(new MessagesRead($receiver->id, $sender->id))->toOthers();
    }
}