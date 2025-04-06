<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\GroupChatController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/verify-auth', [AuthController::class, 'verifyAuth'])->middleware('auth:api');
});

/*
|--------------------------------------------------------------------------
| Chat Routes (Private Chat)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    Route::post('/get-unread-messages', [ChatMessageController::class, 'getUnreadMessages']);
    Route::post('/get-messages', [ChatMessageController::class, 'getMessages']);
    Route::post('/send-message', [ChatMessageController::class, 'store']);
    Route::post('/get-member', [ChatMessageController::class, 'getMember']);
    Route::post('/update-unread', [ChatMessageController::class, 'updateUnread']);
    Route::post('/get-users', [ChatMessageController::class, 'getUsers']);
});

/*
|--------------------------------------------------------------------------
| Group Chat Routes
|--------------------------------------------------------------------------
*/
Route::prefix('group')->middleware('auth:api')->group(function () {
    Route::post('/create-group', [GroupChatController::class, 'createGroup']);
    Route::post('/message', [GroupChatController::class, 'sendMessage']);
    Route::get('/{id}/unseen-messages', [GroupChatController::class, 'getUnseenMessages']);
    Route::post('/{id}/mark-read', [GroupChatController::class, 'markMessagesAsRead']);
    Route::post('/get-messages', [GroupChatController::class, 'getMessages']);
    Route::post('/update-unread', [GroupChatController::class, 'updateUnread']);
    Route::post('/getGroups', [GroupChatController::class, 'getGroups']);
    Route::post('/getConversations', [GroupChatController::class, 'getConversations']);
    Route::post('/info', [GroupChatController::class, 'getInfo']);
    Route::post('/info/users', [GroupChatController::class, 'getInfoUsers']);
    Route::post('/Update-group', [GroupChatController::class, 'updateGroup']);
});


/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::prefix('profile')->middleware('auth:api')->group(function () {
    Route::post('/update', [ProfileController::class, 'update']);
});
