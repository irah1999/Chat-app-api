<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateRequest;
use App\Repositories\Interfaces\GroupChatRepositoryInterface;

class GroupChatController extends Controller
{
    protected $groupChatRepo;

    public function __construct(GroupChatRepositoryInterface $groupChatRepo)
    {
        $this->groupChatRepo = $groupChatRepo;
    }

    public function sendMessage(Request $request)
    {
        $this->groupChatRepo->sendMessage($request);
        return response()->noContent();
    }

    public function getUnseenMessages($groupId)
    {
        $count = $this->groupChatRepo->getUnseenMessages($groupId);
        return response()->json(['unseen_count' => $count]);
    }

    public function markMessagesAsRead($groupId)
    {
        $this->groupChatRepo->markMessagesAsRead($groupId);
        return response()->json(['status' => 'Messages marked as read'], 200);
    }

    public function getMessages(Request $request)
    {
        return $this->groupChatRepo->getMessages($request);
    }

    public function updateUnread(Request $request)
    {
        return $this->groupChatRepo->updateUnread($request);
    }

    public function createGroup(CreateRequest $request)
    {
        return $this->groupChatRepo->createGroup($request);
    }

    public function updateGroup(Request $request)
    {
        return $this->groupChatRepo->updateGroup($request);
    }

    public function getGroups(Request $request)
    {
        return $this->groupChatRepo->getGroups($request);
    }

    public function getConversations(Request $request)
    {
        return $this->groupChatRepo->getConversations($request);
    }

    public function getInfo(Request $request)
    {
        return $this->groupChatRepo->getInfo($request);
    }

    public function getInfoUsers(Request $request)
    {
        return $this->groupChatRepo->getInfoUsers($request);
    }

    

}

