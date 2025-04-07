<?php

namespace App\Repositories;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ChatMessageRepositoryInterface;

class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    public function store(array $data)
    {
        ChatMessage::create($data);
        $receiver = User::find($data['user_id']);
        $sender = User::find($data['from']);
        broadcast(new MessageSent($receiver, $sender, $data['message']));
    }

    public function getUnreadMessages(int $userId)
    {
        return ChatMessage::with('from')->where('user_id', $userId)->get();
    }

    public function getMessages(Request $request)
    {
        $limit  = $request->limit ?? 20;
        $page   = ($request->page ?? 1) - 1;
        $offset = $page * $limit;

        $user_id = $request->user_id;
        $from = Auth::id();

        $messages = ChatMessage::select(
            'id', 'from', 'user_id', 'message as text', 'created_at',
            DB::raw('CASE WHEN `from` = "'.$from.'" THEN "sent" ELSE "received" END as type'),
            DB::raw("DATE_FORMAT(created_at, '%l.%i %p') AS timestamp")
        )
        ->withoutGlobalScopes()
        ->where(function ($query) use ($user_id, $from) {
            $query->where(function ($query) use ($user_id, $from) {
                $query->where('from', $from)->where('user_id', $user_id);
            })->orWhere(function ($query) use ($user_id, $from) {
                $query->where('from', $user_id)->where('user_id', $from);
            });
        });

        $totalCount = $messages->count();
        $totalPages = ceil($totalCount / $limit);

        $data = $messages->orderBy('id', 'desc')
                    ->limit($limit)
                    ->offset($offset)
                    ->get()
                    ->toArray();

        return [
            'total' => $totalCount,
            'limit' => $limit,
            'page' => $page + 1,
            'total_pages' => $totalPages,
            'data' => array_reverse($data),
        ];
    }

    public function getMember(Request $request)
    {
        $limit = $request->limit ?? 10;
        $page = ($request->page ?? 1) - 1;
        $offset = $page * $limit;
        $loggedInUserId = Auth::id();

        $usersQuery = User::select('users.*')
            ->selectSub(function ($query) use ($loggedInUserId) {
                $query->from('chat_messages')
                    ->whereColumn('users.id', 'chat_messages.from')
                    ->where('un_read', 0)
                    ->where('chat_messages.user_id', $loggedInUserId)
                    ->selectRaw('count(*)');
            }, 'unread_messages')
            ->selectSub(function ($query) use ($loggedInUserId) {
                $query->from('chat_messages')
                    ->whereColumn('users.id', 'chat_messages.from')
                    ->where('chat_messages.user_id', $loggedInUserId)
                    ->orderByDesc('chat_messages.id')
                    ->limit(1)
                    ->select('chat_messages.message');
            }, 'last_message')
            ->where('users.id', '!=', $loggedInUserId)
            ->orderBy('users.id', 'asc')
            ->limit($limit)
            ->offset($offset);

        $totalCount = User::where('id', '!=', $loggedInUserId)->count();
        $totalPages = ceil($totalCount / $limit);

        return [
            'total' => $totalCount,
            'limit' => $limit,
            'page' => $page + 1,
            'total_pages' => $totalPages,
            'data' => $usersQuery->get()
        ];
    }

    public function updateUnread(int $from, int $userId)
    {
        return ChatMessage::where('user_id', $userId)
            ->where('from', $from)
            ->update(['un_read' => 1]);
    }

    public function getUsers()
    {
        return User::select('id', 'name', 'image')->get();
    }
}

