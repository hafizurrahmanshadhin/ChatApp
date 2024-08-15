<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSendEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller {
    public function getUsers() {
        $users = User::where('id', '!=', auth()->id())->get();
        return UserResource::collection($users);
    }

    public function getMessages($userId) {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', auth()->id());
        })->with('sender:id,name', 'receiver:id,name')->get();

        return $messages;
    }

    public function sendMessage(Request $request) {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string',
        ]);

        if ($request->receiver_id == auth()->id()) {
            return response()->json(['error' => 'You cannot send message to yourself.'], 422);
        }

        $chatMessage              = new Message();
        $chatMessage->sender_id   = auth()->id();
        $chatMessage->receiver_id = $request->receiver_id;
        $chatMessage->message     = $request->message;
        $chatMessage->save();

        broadcast(new MessageSendEvent($chatMessage))->toOthers();

        return $chatMessage->load('sender:id,name', 'receiver:id,name');
    }
}
