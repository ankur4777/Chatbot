<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\Website;
use App\Support\BrowserTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardExportController extends Controller
{
    public function companies(Request $request): StreamedResponse
    {
        $this->authorizeSuperAdmin($request);

        $filename = 'companies-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Company ID',
                'Company',
                'Email',
                'Phone',
                'Slug',
                'Status',
                'Websites',
                'Website Names',
                'Users',
                'Created At',
                'Updated At',
            ]);

            Company::query()
                ->with(['websites:id,company_id,name'])
                ->withCount(['websites', 'users'])
                ->orderBy('name')
                ->chunk(100, function ($companies) use ($handle): void {
                    foreach ($companies as $company) {
                        fputcsv($handle, [
                            $company->id,
                            $company->name,
                            $company->email ?: 'N/A',
                            $company->phone ?: 'N/A',
                            $company->slug ?: 'N/A',
                            $company->status ? 'Active' : 'Inactive',
                            $company->websites_count,
                            $company->websites->pluck('name')->filter()->implode(', ') ?: 'N/A',
                            $company->users_count,
                            BrowserTime::format($company->created_at),
                            BrowserTime::format($company->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function websites(Request $request): StreamedResponse
    {
        $this->authorizeSuperAdmin($request);

        $filename = 'websites-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Website ID',
                'Company',
                'Website',
                'Domain',
                'Widget Key',
                'Status',
                'Created At',
                'Updated At',
            ]);

            Website::query()
                ->with('company')
                ->orderBy('name')
                ->chunk(100, function ($websites) use ($handle): void {
                    foreach ($websites as $website) {
                        fputcsv($handle, [
                            $website->id,
                            $website->company?->name ?? 'N/A',
                            $website->name,
                            $website->domain ?: 'N/A',
                            $website->widget_key ?: 'N/A',
                            $website->status ? 'Active' : 'Inactive',
                            BrowserTime::format($website->created_at),
                            BrowserTime::format($website->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function users(Request $request): StreamedResponse
    {
        $this->authorizeSuperAdmin($request);

        $filename = 'users-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'User ID',
                'Company',
                'Name',
                'Email',
                'Phone',
                'Role',
                'Status',
                'Last Seen At',
                'Created At',
                'Updated At',
            ]);

            User::query()
                ->with('company')
                ->orderBy('name')
                ->chunk(100, function ($users) use ($handle): void {
                    foreach ($users as $user) {
                        fputcsv($handle, [
                            $user->id,
                            $user->company?->name ?? 'N/A',
                            $user->name,
                            $user->email,
                            $user->phone ?: 'N/A',
                            $this->roleLabel($user->role),
                            $user->status ? 'Active' : 'Inactive',
                            $user->last_seen_at ? BrowserTime::format($user->last_seen_at) : 'N/A',
                            BrowserTime::format($user->created_at),
                            BrowserTime::format($user->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'super_admin', 403);
    }

    protected function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'owner' => 'Client',
            'agent' => 'Agent',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }
}
