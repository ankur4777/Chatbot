<?php

use App\Models\User;
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
