<?php

use App\Models\User;
use App\Http\Controllers\Agent\AuthController as AgentAuthController;
use App\Http\Controllers\Agent\LiveChatController as AgentLiveChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

        Route::middleware('agent')->group(function () {
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

            Route::get('/closed/{session}', [AgentLiveChatController::class, 'showClosedSession'])
                ->name('closed.show');

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

            Route::get('/chats/{conversation}/attachments/{message}', [AgentLiveChatController::class, 'showAttachment'])
                ->name('chats.attachments.show');

            Route::post('/chats/{conversation}/close', [AgentLiveChatController::class, 'close'])
                ->name('chats.close');
        });
    });
