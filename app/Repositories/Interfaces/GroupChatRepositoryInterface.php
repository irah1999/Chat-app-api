<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface GroupChatRepositoryInterface
{
    public function sendMessage(Request $request);
    public function getUnseenMessages(int $groupId);
    public function markMessagesAsRead(int $groupId);
    public function getMessages(Request $request);
    public function updateUnread(Request $request);
    public function createGroup(Request $request);
    public function getGroups(Request $request);
    public function getConversations(Request $request);
    public function getInfo(Request $request);
    public function getInfoUsers(Request $request);
    public function updateGroup(Request $request);
    
}

