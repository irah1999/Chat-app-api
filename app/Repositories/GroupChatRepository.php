<?php

namespace App\Repositories;

use App\Repositories\Interfaces\GroupChatRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\GroupMessage;
use App\Models\GroupMessagesReads;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use App\Events\GroupMessageSent;
use Illuminate\Validation\ValidationException;

class GroupChatRepository implements GroupChatRepositoryInterface
{
    public function sendMessage(Request $request)
    {
        $from = Auth::user();

        $message = GroupMessage::create([
            'group_id' => $request->group_id,
            'user_id' => $from->id,
            'message' => $request->message,
            'is_read' => false
        ]);

        GroupMessagesReads::create([
            'group_id' => $request->group_id,
            'user_id' => $from->id,
            'message_id' => $message->id,
        ]);

        broadcast(new GroupMessageSent($from, $request->message, $request->group_id, $message->id))->toOthers();

        return response()->noContent();
    }

    public function getUnseenMessages($groupId)
    {
        $count = GroupMessage::where('group_id', $groupId)
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unseen_count' => $count]);
    }

    public function markMessagesAsRead($groupId)
    {
        GroupMessage::where('group_id', $groupId)
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'Messages marked as read']);
    }

    public function getMessages(Request $request)
    {
        $limit = $request->limit ?? 10;
        $page = ($request->page ?? 1) - 1;
        $offset = $page * $limit;
        $from = Auth::id();

        $messagesQuery = GroupMessage::select(
            'group_messages.id',
            'group_messages.user_id',
            'users.name AS sender',
            'group_messages.message AS text',
            'group_messages.created_at',
            DB::raw("users.image"),
            DB::raw("DATE_FORMAT(group_messages.created_at, '%l.%i %p') AS timestamp"),
            DB::raw("CASE WHEN group_messages.user_id = $from THEN 'sent' ELSE 'received' END as type")
        )
        ->join('users', 'users.id', '=', 'group_messages.user_id')
        ->withoutGlobalScopes()
        ->where('group_messages.group_id', $request->group_id);

        $totalCount = $messagesQuery->count();
        $totalPages = ceil($totalCount / $limit);

        $messages = $messagesQuery
            ->orderBy('group_messages.id', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();

        return response()->json([
            'total' => $totalCount,
            'limit' => $limit,
            'page' => $page + 1,
            'total_pages' => $totalPages,
            'data' => array_reverse($messages),
        ]);
    }

    public function updateUnread(Request $request)
    {
        if (!empty($request->group_id)) {
            if (!empty($request->message_id)) {
                GroupMessagesReads::create([
                    'group_id' => $request->group_id,
                    'user_id' => Auth::user()->id,
                    'message_id' => $request->message_id,
                ]);
            } else {
                $data = [];
                $messages = GroupMessage::where('group_id', $request->group_id)->get();

                foreach ($messages as $key => $message) {
                    $data[$key]['message_id'] = $message['id'];
                    $data[$key]['group_id'] = $request->group_id;
                    $data[$key]['user_id'] = Auth::user()->id;
                }

                foreach ($data as $row) {
                    GroupMessagesReads::updateOrInsert(
                        [
                            'message_id' => $row['message_id'],
                            'group_id' => $row['group_id'],
                            'user_id' => $row['user_id'],
                        ],
                        ['is_read' => true]
                    );
                }
            }
        }

        return response()->json([
            'message' => 'Message Unread updated successfully',
            'success' => true
        ]);
    }

    public function createGroup(Request $request)
    {
        try {
            $group = Group::create([
                'name' => $request->name,
                'status' => "1",
                'created_by' => Auth::user()->id
            ]);

            $userData = [];
            foreach ($request->user as $key => $value) {
                $userData[$key]['group_id'] = $group->id;
                $userData[$key]['user_id'] = $value;
            }
            GroupUser::insert($userData);

            return response()->json([
                'message' => 'Successfully created',
                'success' => true,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }
    }

    public function getGroups(Request $request)
    {
        $limit  = $request->limit ?? 10;
        $page   = ($request->page ?? 1) - 1;
        $offset = $page * $limit;

        $userId = Auth::user()->id;

        $group = DB::table('groups as g')
            ->join('group_users as gu', 'gu.group_id', '=', 'g.id')
            ->join('group_messages as gm', 'gm.group_id', '=', 'g.id')
            ->leftJoin('group_messages_reads as gmr', function ($join) {
                $join->on('gmr.message_id', '=', 'gm.id')
                    ->on('gmr.user_id', '=', 'gu.user_id');
            })
            ->select([
                'g.name',
                'gu.user_id',
                DB::raw("COUNT(CASE WHEN gmr.id IS NULL THEN 1 END) as unread_messages"),
                DB::raw("(SELECT gm2.message FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1) as last_message"),
                DB::raw("(SELECT DATE_FORMAT(gm2.created_at, '%l.%i %p') FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1) as last_message_time"),
            ])
            ->groupBy('g.id', 'gu.user_id')
            ->having('gu.user_id', '=', $userId)
            ->orderBy('g.id')
            ->orderBy('gu.user_id');

        $totalCount = $group->count();
        $groups = $group->offset($offset)->limit($limit)->get();
        $totalPages = ceil($totalCount / $limit);

        return response()->json([
            'total_records' => $totalCount,
            'limit' => $limit,
            'page' => $page + 1,
            'total_pages' => $totalPages,
            'data' => $groups,
        ]);
    }

    public function getConversations(Request $request)
    {
        $limit = $request->limit ?? 10;
        $page = ($request->page ?? 1) - 1;
        $offset = $page * $limit;
        $userId = Auth::id();
        $search = $request->search;
    
        // Private Conversations
        $private = User::selectRaw("'private' as type, name, id as user_id, image, '0' as group_id")
            ->selectSub(function ($q) use ($userId) {
                $q->from('chat_messages')
                    ->whereColumn('chat_messages.from', 'users.id')
                    ->where('chat_messages.user_id', $userId)
                    ->where('un_read', 0)
                    ->selectRaw('count(*)');
            }, 'unread_messages')
            ->selectSub(function ($q) use ($userId) {
                $q->from('chat_messages')
                    ->whereColumn('chat_messages.from', 'users.id')
                    ->where('chat_messages.user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(1)
                    ->select('message');
            }, 'last_message')
            ->selectSub(function ($q) use ($userId) {
                $q->from('chat_messages')
                    ->whereColumn('chat_messages.from', 'users.id')
                    ->where('chat_messages.user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(1)
                    ->selectRaw("DATE_FORMAT(created_at, '%l.%i %p')");
            }, 'last_message_time')
            ->selectSub(function ($q) use ($userId) {
                $q->from('chat_messages')
                    ->whereColumn('chat_messages.from', 'users.id')
                    ->where('chat_messages.user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(1)
                    ->select('created_at');
            }, 'last_message_created_at')
            ->addSelect(DB::raw("COALESCE((SELECT created_at FROM chat_messages WHERE chat_messages.from = users.id AND chat_messages.user_id = $userId ORDER BY id DESC LIMIT 1), users.created_at) as last_active"))
            ->where('id', '!=', $userId);
    
        // 👇 Add search filter for private (user name)
        if ($search) {
            $private->where('users.name', 'like', "%$search%");
        }
    
        // Group Conversations
        $group = DB::table('groups as g')
            ->join('group_users as gu', 'gu.group_id', '=', 'g.id')
            ->leftJoin('group_messages as gm', 'gm.group_id', '=', 'g.id')
            ->leftJoin('group_messages_reads as gmr', function ($join) {
                $join->on('gmr.message_id', '=', 'gm.id')
                    ->on('gmr.user_id', '=', 'gu.user_id');
            })
            ->where('gu.user_id', $userId)
            ->selectRaw("'group' as type")
            ->selectRaw('g.name')
            ->selectRaw("gu.user_id as user_id")
            ->selectRaw("IFNULL(g.image, '') as image")
            ->selectRaw('g.id as group_id')
            ->selectRaw("COUNT(CASE WHEN gmr.id IS NULL THEN 1 END) as unread_messages")
            ->selectRaw("(SELECT gm2.message FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1) as last_message")
            ->selectRaw("(SELECT DATE_FORMAT(gm2.created_at, '%l.%i %p') FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1) as last_message_time")
            ->selectRaw("(SELECT gm2.created_at FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1) as last_message_created_at")
            ->selectRaw("COALESCE((SELECT gm2.created_at FROM group_messages gm2 WHERE gm2.group_id = g.id ORDER BY gm2.id DESC LIMIT 1), g.created_at) as last_active")
            ->groupBy('g.id', 'gu.user_id');
    
        // 👇 Add search filter for group name
        if ($search) {
            $group->where('g.name', 'like', "%$search%");
        }
    
        // Combine both
        $combined = $private->unionAll($group);
    
        $all = DB::query()->fromSub($combined, 'conversations')
            ->orderByDesc('last_active');
    
        $total = $all->count();
        $results = $all->offset($offset)->limit($limit)->get();
    
        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'page' => $page + 1,
            'total_pages' => ceil($total / $limit),
            'data' => $results
        ]);
    }
    

    public function getInfo(Request $request)
    {
        try {
            $group = Group::with('users')->findOrFail($request->group_id);
    
            return response()->json([
                'success' => true,
                'group' => $group->only(['id', 'name', 'image']),
                'users' => $group->users,
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'users' => [],
            ], 400);
        }
    }

    public function getInfoUsers(Request $request)
    {
        try {
            $group = Group::with('users')->findOrFail($request->group_id);
    
            // Already added user IDs - ensure they're integers
            $existingUserIds = $group->users->pluck('id')->map(fn($id) => (int) $id)->toArray();

            // Page and limit from request (default fallback)
            $page = (int) $request->input('page', 1);
            $limit = (int) $request->input('limit', 20);
    
            // Fetch only users who are not in this group
            $query = User::whereNotIn('id', $existingUserIds)
                ->orderBy('name');
    
            $users = $query->paginate($limit, ['*'], 'page', $page);
    
            return response()->json([
                'success' => true,
                'group' => $group->only(['id', 'name', 'image']),
                'group_users' => $group->users, // users already in group
                'users' => $users->items(),     // eligible users
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'users' => [],
            ], 400);
        }
    }

    public function updateGroup(Request $request)
    {
        try {
            $validated = $request->validate([
                'group_id' => 'required|exists:groups,id',
                'name' => 'required|string|max:255',
                'user' => 'array',
                'user.*' => 'exists:users,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            $group = Group::findOrFail($validated['group_id']);
            $group->name = $validated['name'];
    
            // Update group image if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('profile_images', 'public');
                $group->image = $imagePath;
            }
    
            $group->save();
    
            // Sync only new users (add without removing existing ones)
            if (!empty($validated['user'])) {
                $existingUserIds = $group->users()->pluck('users.id')->toArray();
                $newUserIds = array_diff($validated['user'], $existingUserIds);
    
                if (!empty($newUserIds)) {
                    $insertData = array_map(fn($userId) => [
                        'group_id' => $group->id,
                        'user_id' => $userId,
                    ], $newUserIds);
    
                    GroupUser::insert($insertData);
                }
            }
    
            return response()->json([
                'message' => 'Group updated successfully',
                'success' => true,
                'group' => $group,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}

