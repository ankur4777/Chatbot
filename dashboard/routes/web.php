<?php

use App\Models\User;
use App\Models\ChatMessage;
use App\Http\Controllers\Admin\DashboardExportController;
use App\Http\Controllers\Agent\AuthController as AgentAuthController;
use App\Http\Controllers\Agent\LiveChatController as AgentLiveChatController;
use App\Http\Controllers\Agent\MissedChatController as AgentMissedChatController;
use App\Http\Controllers\Agent\NotificationController as AgentNotificationController;
use App\Http\Controllers\Agent\ProfileController as AgentProfileController;
use App\Http\Controllers\Client\AgentExportController;
use App\Http\Controllers\Client\ChatbotConversationExportController;
use App\Http\Controllers\Client\ChatbotLeadExportController;
use App\Http\Controllers\Client\ClosedChatExportController;
use App\Http\Controllers\Client\VisitorExportController;
use App\Http\Controllers\Client\WebsiteExportController;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetBrowserTimezone;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/users/{user}/view-client-dashboard', function (Request $request, User $user) {
    $admin = Auth::guard('web')->user();

    abort_unless($admin?->role === 'super_admin', 403);
    abort_unless($user->role === 'owner' && $user->status && $user->company?->status, 403);

    $request->session()->put('impersonator_id', $admin->getKey());

    Auth::guard('web')->login($user);
    $request->session()->regenerate();
    $request->session()->put('password_hash_web', $user->getAuthPassword());

    return redirect('/client');
})
    ->middleware(['signed:relative'])
    ->name('admin.users.view-client-dashboard');

Route::get('/admin/download/companies', [DashboardExportController::class, 'companies'])
    ->middleware('auth')
    ->name('admin.companies.download');

Route::get('/admin/download/websites', [DashboardExportController::class, 'websites'])
    ->middleware('auth')
    ->name('admin.websites.download');

Route::get('/admin/download/users', [DashboardExportController::class, 'users'])
    ->middleware('auth')
    ->name('admin.users.download');

Route::get('/client/agents/{user}/view-agent-dashboard', function (Request $request, User $user) {
    $owner = Auth::guard('web')->user();

    abort_unless($owner?->role === 'owner' && $owner->company_id, 403);
    abort_unless(
        $user->role === 'agent'
        && $user->status
        && $user->company_id === $owner->company_id
        && $user->company?->status,
        403
    );

    $request->session()->put('impersonator_id', $owner->getKey());

    Auth::guard('web')->login($user);
    $request->session()->regenerate();
    $request->session()->put('password_hash_web', $user->getAuthPassword());

    return redirect('/agent');
})
    ->middleware(['signed:relative'])
    ->name('client.agents.view-agent-dashboard');

Route::get('/client/chat-attachments/{message}', function (Request $request, ChatMessage $message, ChatService $chatService) {
    $user = $request->user();

    abort_unless($user?->role === 'owner' && $user->company_id, 403);
    abort_unless($message->attachment, 404);
    abort_unless(
        $message->conversation()
            ->whereHas(
                'website',
                fn ($query) => $query->where('company_id', $user->company_id)
            )
            ->exists(),
        404
    );

    return $chatService->attachmentResponse($message);
})
    ->middleware('auth')
    ->name('client.chat-attachments.show');

Route::get('/client/closed-chats/download', [ClosedChatExportController::class, 'downloadAll'])
    ->middleware('auth')
    ->name('client.closed-chats.download-all');

Route::get('/client/closed-chats/{session}/download', [ClosedChatExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.closed-chats.download');

Route::get('/client/agents/download', [AgentExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.agents.download');

Route::get('/client/websites/download', [WebsiteExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.websites.download');

Route::get('/client/visitors/download', [VisitorExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.visitors.download');

Route::get('/client/chatbot-leads/download', [ChatbotLeadExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.chatbot-leads.download');

Route::get('/client/missed-chats/download', [ChatbotLeadExportController::class, 'downloadMissedChats'])
    ->middleware('auth')
    ->name('client.missed-chats.download');

Route::get('/client/chatbot-conversations/download', [ChatbotConversationExportController::class, 'download'])
    ->middleware('auth')
    ->name('client.chatbot-conversations.download');

Route::get('/client/chatbot-conversations/{conversation}/download', [ChatbotConversationExportController::class, 'downloadConversation'])
    ->middleware('auth')
    ->name('client.chatbot-conversations.download-one');

Route::prefix('agent')
    ->name('agent.')
    ->group(function () {
        Route::get('/login', [AgentAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AgentAuthController::class, 'login'])
            ->name('login.store');

        Route::get('/forgot-password', [AgentAuthController::class, 'showForgotPassword'])
            ->name('password.request');

        Route::post('/forgot-password', [AgentAuthController::class, 'sendPasswordResetLink'])
            ->middleware('throttle:5,1')
            ->name('password.email');

        Route::get('/reset-password/{token}', [AgentAuthController::class, 'showResetPassword'])
            ->name('password.reset');

        Route::post('/reset-password', [AgentAuthController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.update');

        Route::post('/logout', [AgentAuthController::class, 'logout'])
            ->middleware('agent')
            ->name('logout');

        Route::middleware(['agent', SetBrowserTimezone::class])->group(function () {
            Route::get('/', [AgentLiveChatController::class, 'dashboard'])
                ->name('dashboard');

            Route::get('/waiting', [AgentLiveChatController::class, 'waiting'])
                ->name('waiting');

            Route::get('/chats', [AgentLiveChatController::class, 'chats'])
                ->name('chats');

            Route::get('/chats/{conversation}', [AgentLiveChatController::class, 'show'])
                ->name('chats.show');

            Route::get('/closed', [AgentLiveChatController::class, 'closed'])
                ->name('closed');

            Route::get('/missed-chats', [AgentMissedChatController::class, 'index'])
                ->name('missed-chats');

            Route::get('/notifications', [AgentNotificationController::class, 'index'])
                ->name('notifications');

            Route::get('/notifications/latest', [AgentNotificationController::class, 'latest'])
                ->name('notifications.latest');

            Route::post('/notifications/mark-all-read', [AgentNotificationController::class, 'markAllRead'])
                ->name('notifications.mark-all-read');

            Route::post('/notifications/bulk', [AgentNotificationController::class, 'bulk'])
                ->name('notifications.bulk');

            Route::delete('/notifications/clear-read', [AgentNotificationController::class, 'clearRead'])
                ->name('notifications.clear-read');

            Route::delete('/notifications/{notification}', [AgentNotificationController::class, 'destroy'])
                ->name('notifications.destroy');

            Route::get('/missed-chats/{lead}', [AgentMissedChatController::class, 'show'])
                ->name('missed-chats.show');

            Route::patch('/missed-chats/{lead}', [AgentMissedChatController::class, 'update'])
                ->name('missed-chats.update');

            Route::get('/profile', [AgentProfileController::class, 'show'])
                ->name('profile');

            Route::get('/canned-replies', [AgentLiveChatController::class, 'cannedReplies'])
                ->name('canned-replies');

            Route::post('/canned-replies', [AgentLiveChatController::class, 'storeCannedReply'])
                ->name('canned-replies.store');

            Route::patch('/canned-replies/{reply}', [AgentLiveChatController::class, 'updateCannedReply'])
                ->name('canned-replies.update');

            Route::delete('/canned-replies/{reply}', [AgentLiveChatController::class, 'deleteCannedReply'])
                ->name('canned-replies.delete');

            Route::get('/closed/{session}', [AgentLiveChatController::class, 'showClosedSession'])
                ->name('closed.show');

            Route::get('/closed/{session}/download', [AgentLiveChatController::class, 'downloadClosedSession'])
                ->name('closed.download');

            Route::get('/closed/{session}/note', [AgentLiveChatController::class, 'editClosedSessionNote'])
                ->name('closed.note.edit');

            Route::patch('/closed/{session}/note', [AgentLiveChatController::class, 'updateClosedSessionNote'])
                ->name('closed.note.update');

            Route::post('/availability', [AgentLiveChatController::class, 'updateAvailability'])
                ->name('availability.update');

            Route::post('/chats/{conversation}/accept', [AgentLiveChatController::class, 'accept'])
                ->name('chats.accept');

            Route::post('/chats/{conversation}/messages', [AgentLiveChatController::class, 'sendMessage'])
                ->name('chats.messages');

            Route::post('/chats/{conversation}/status', [AgentLiveChatController::class, 'updateChatStatus'])
                ->name('chats.status');

            Route::patch('/chats/{conversation}/visitor', [AgentLiveChatController::class, 'updateVisitorDetails'])
                ->name('chats.visitor.update');

            Route::get('/chats/{conversation}/attachments/{message}', [AgentLiveChatController::class, 'showAttachment'])
                ->name('chats.attachments.show');

            Route::post('/chats/{conversation}/close', [AgentLiveChatController::class, 'close'])
                ->name('chats.close');
        });
    });
