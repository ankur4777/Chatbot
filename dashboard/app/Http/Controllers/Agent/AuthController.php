<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use App\Notifications\AgentResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use App\Services\LiveChatAvailabilityService;
use App\Services\LiveChatService;

class AuthController extends Controller
{
    public function __construct(
        protected LiveChatAvailabilityService $availabilityService,
        protected LiveChatService $liveChatService
    ) {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'agent') {
            return redirect()->route('agent.dashboard');
        }

        return view('agent.auth.login');
    }

    public function showForgotPassword(): View
    {
        return view('agent.auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::broker()->sendResetLink(
            [
                'email' => $data['email'],
                'role' => 'agent',
                'status' => true,
            ],
            function (User $user, string $token): void {
                if (
                    $user->role !== 'agent'
                    || ! $user->status
                    || ! $user->company_id
                    || ! $user->company?->status
                ) {
                    return;
                }

                $user->notify(
                    new AgentResetPasswordNotification($token)
                );
            }
        );

        return back()->with(
            'status',
            'If an agent account exists for this email, a password reset link has been sent.'
        );
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('agent.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::defaults(),
            ],
        ]);

        $agent = User::query()
            ->where('email', $data['email'])
            ->where('role', 'agent')
            ->where('status', true)
            ->first();

        if (
            ! $agent
            || ! $agent->company_id
            || ! $agent->company?->status
        ) {
            return back()
                ->withErrors([
                    'email' => 'Unable to reset password for this agent account.',
                ])
                ->withInput($request->only('email'));
        }

        $status = Password::broker()->reset(
            [
                'email' => $data['email'],
                'role' => 'agent',
                'status' => true,
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                if (
                    $user->role !== 'agent'
                    || ! $user->status
                    || ! $user->company_id
                    || ! $user->company?->status
                ) {
                    return;
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors([
                    'email' => 'Unable to reset password for this agent account.',
                ])
                ->withInput($request->only('email'));
        }

        return redirect()
            ->route('agent.login')
            ->with(
                'status',
                'Your password has been reset successfully. You can now log in.'
            );
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'The provided credentials are invalid.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (
            $user->role !== 'agent'
            || ! $user->status
            || ! $user->company_id
            || ! $user->company?->status
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'This account cannot access the agent dashboard.',
                ])
                ->onlyInput('email');
        }

        $this->availabilityService->setAvailability($user, 'online');

        return redirect()->intended(route('agent.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $agent = $request->user();

        if ($agent?->role === 'agent') {
            $this->closeAssignedLiveChats($agent);

            $this->availabilityService->setAvailability(
                $agent,
                'offline'
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agent.login');
    }

    protected function closeAssignedLiveChats($agent): void
    {
        ChatConversation::query()
            ->with('website.settings')
            ->where('status', 'live_active')
            ->where('assigned_agent_id', $agent->id)
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $agent->company_id
                )
            )
            ->each(function (ChatConversation $conversation) use ($agent): void {
                try {
                    $this->liveChatService->closeConversationAsAgent(
                        $conversation,
                        $agent
                    );
                } catch (\Throwable $exception) {
                    Log::warning(
                        'Unable to close live chat during agent logout.',
                        [
                            'agent_id' => $agent->id,
                            'conversation_id' => $conversation->id,
                            'message' => $exception->getMessage(),
                        ]
                    );
                }
            });
    }
}
