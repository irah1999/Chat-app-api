<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ChatMessageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatMessageController extends Controller
{
    public function __construct(protected ChatMessageRepositoryInterface $chatMessageRepo) {}

    public function store(Request $request): Response
    {
        $this->chatMessageRepo->store($request->toArray());
        return response()->noContent();
    }

    public function getUnreadMessages(Request $request)
    {
        return $this->chatMessageRepo->getUnreadMessages($request->user_id);
    }

    public function getMessages(Request $request)
    {
        return response()->json($this->chatMessageRepo->getMessages($request));
    }

    public function getMember(Request $request)
    {
        return response()->json($this->chatMessageRepo->getMember($request));
    }

    public function updateUnread(Request $request)
    {
        $updated = $this->chatMessageRepo->updateUnread($request->from, $request->user_id);
        return response()->json([
            'message' => 'Message Unread updated successfully',
            'success' => true,
            'updated' => $updated
        ]);
    }

    public function getUsers()
    {
        return response()->json([
            'message' => 'success',
            'success' => true,
            'data' => $this->chatMessageRepo->getUsers()
        ]);
    }
}

