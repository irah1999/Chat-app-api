<?php

namespace App\Repositories\Interfaces;


use Illuminate\Http\Request;

interface ChatMessageRepositoryInterface
{
    public function store(array $data);
    public function getUnreadMessages(int $userId);
    public function getMessages(Request $request);
    public function getMember(Request $request);
    public function updateUnread(int $from, int $userId);
    public function getUsers();
}
