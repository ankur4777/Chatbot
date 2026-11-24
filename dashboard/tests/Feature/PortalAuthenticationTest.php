<?php

namespace Tests\Feature;

use App\Http\Controllers\PortalLogoutController;
use App\Http\Middleware\SelectPortalGuard;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PortalAuthenticationTest extends TestCase
{
    public function test_three_portal_guards_keep_independent_login_keys(): void
    {
        $accounts = [
            'admin' => $this->account(1, 'super_admin'),
            'client' => $this->account(2, 'owner'),
            'agent' => $this->account(3, 'agent'),
        ];

        foreach ($accounts as $guard => $user) {
            Auth::guard($guard)->login($user);
        }

        $keys = [];
        foreach ($accounts as $guard => $user) {
            $keys[] = Auth::guard($guard)->getName();
            $this->assertSame($user->id, session()->get(Auth::guard($guard)->getName()));
            $this->assertSame($user->id, Auth::guard($guard)->id());

            app(SelectPortalGuard::class)->handle(
                Request::create('/' . $guard, 'GET'),
                function () use ($guard, $user) {
                    $this->assertSame($guard, Auth::getDefaultDriver());
                    $this->assertSame($user->id, Auth::id());

                    return response('ok');
                }
            );
        }

        $this->assertCount(3, array_unique($keys));
    }

    public function test_admin_and_client_logout_do_not_clear_other_portals(): void
    {
        foreach (['admin' => 'super_admin', 'client' => 'owner', 'agent' => 'agent'] as $guard => $role) {
            Auth::guard($guard)->login($this->account(
                match ($guard) { 'admin' => 1, 'client' => 2, default => 3 },
                $role
            ));
        }

        foreach (['admin', 'client'] as $guard) {
            Filament::setCurrentPanel(Filament::getPanel($guard));
            Auth::shouldUse($guard);
            app(\Filament\Auth\Http\Controllers\LogoutController::class)->__invoke();

            $this->assertFalse(Auth::guard($guard)->check());
            $this->assertFalse(session()->has(Auth::guard($guard)->getName()));
            $this->assertSame(3, session()->get(Auth::guard('agent')->getName()));
            $this->assertTrue(Auth::guard('agent')->check());

            if ($guard === 'admin') {
                $this->assertSame(2, session()->get(Auth::guard('client')->getName()));
                $this->assertTrue(Auth::guard('client')->check());
            }
        }
    }

    public function test_wrong_role_in_client_guard_is_rejected_without_admin_logout(): void
    {
        $admin = $this->account(1, 'super_admin');
        Auth::guard('admin')->login($admin);
        Auth::guard('client')->login($admin);

        Filament::setCurrentPanel(Filament::getPanel('client'));
        Auth::shouldUse('client');

        $request = Request::create('/client', 'GET');
        $request->setLaravelSession(session()->driver());

        try {
            app(\App\Http\Middleware\AuthenticatePortal::class)->handle(
                $request,
                fn () => response('unexpected')
            );
            $this->fail('A super admin must not be authenticated as a client.');
        } catch (\Illuminate\Auth\AuthenticationException $exception) {
            $this->assertSame(['client'], $exception->guards());
        }

        $this->assertFalse(Auth::guard('client')->check());
        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertSame(1, session()->get(Auth::guard('admin')->getName()));
    }

    private function account(int $id, string $role): User
    {
        $user = new User();
        $user->forceFill(['id' => $id, 'role' => $role, 'status' => true]);

        return $user;
    }
}
