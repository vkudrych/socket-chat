<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $messageService) {}

    public function send(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        $message = $this->messageService->send(
            auth()->id(),
            (int) $request->receiver_id,
            $request->message
        );

        if ($request->expectsJson()) {
            return response()->json($message);
        }

        return back();
    }
}