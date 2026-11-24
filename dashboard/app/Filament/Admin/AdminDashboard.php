<?php

namespace App\Filament\Admin;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\LiveChatSettings\LiveChatSettingResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Websites\WebsiteResource;
use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use App\Models\Company;
use App\Models\User;
use App\Models\Website;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

class AdminDashboard extends Dashboard
{
    protected static ?string $title = 'Overview';

    protected static ?string $navigationLabel = 'Overview';

    protected string $view = 'filament.admin.pages.dashboard';

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    protected function getViewData(): array
    {
        abort_unless(auth('admin')->user()?->role === 'super_admin', 403);

        return [
            'adminName' => str(auth('admin')->user()->name)->before(' ')->toString(),
            'companyCount' => Company::query()->count(),
            'activeCompanyCount' => Company::query()->where('status', true)->count(),
            'websiteCount' => Website::query()->count(),
            'activeWebsiteCount' => Website::query()->where('status', true)->count(),
            'userCount' => User::query()->count(),
            'ownerCount' => User::query()->where('role', 'owner')->count(),
            'agentCount' => User::query()->where('role', 'agent')->count(),
            'newCompaniesCount' => Company::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'recentCompanies' => Company::query()
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'name', 'status', 'created_at']),
            'recentWebsites' => Website::query()
                ->with('company:id,name')
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'company_id', 'name', 'domain', 'status', 'created_at']),
            'links' => [
                'companies' => CompanyResource::getUrl('index', panel: 'admin'),
                'newCompany' => CompanyResource::getUrl('create', panel: 'admin'),
                'users' => UserResource::getUrl('index', panel: 'admin'),
                'websites' => WebsiteResource::getUrl('index', panel: 'admin'),
                'chatbotSettings' => WebsiteSettingResource::getUrl('index', panel: 'admin'),
                'liveChatSettings' => LiveChatSettingResource::getUrl('index', panel: 'admin'),
            ],
        ];
    }
}
