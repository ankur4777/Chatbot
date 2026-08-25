<?php

namespace App\Filament\Client\Resources\ChatMessages\Pages;

use App\Filament\Client\Resources\ChatMessages\ChatMessageResource;
use App\Models\ChatConversation;
use App\Models\Visitor;
use App\Models\Website;
use Filament\Actions\Action;
use App\Support\BrowserTime;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteVisitors extends ListRecords
{
    protected static string $resource = ChatMessageResource::class;

    public int $websiteId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        // Tenant security check
        $this->getWebsite();

        parent::mount();
    }

    protected function getWebsite(): Website
    {
        $user = auth()->user();

        return Website::query()
            ->whereKey($this->websiteId)
            ->where('company_id', $user->company_id)
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->getWebsite()->name . ' Visitors';
    }

    protected function getTableQuery(): Builder
    {
        return Visitor::query()
            ->where('website_id', $this->websiteId)
            ->whereNotNull('visitor_uuid')
            ->where('visitor_uuid', '!=', '');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([

                TextColumn::make('visitor_uuid')
                    ->label('Visitor')
                    ->formatStateUsing(
                        fn ($state) =>
                            $state
                                ? 'Visitor ' . substr($state, 0, 8)
                                : '—'
                    )
                    ->tooltip(
                        fn ($record) =>
                            $record->visitor_uuid
                                ?: 'No Visitor ID'
                    )
                    ->copyable()
                    ->copyableState(
                        fn ($record) =>
                            $record->visitor_uuid
                    )
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),

                TextColumn::make('total_conversations')
                    ->label('Conversations')
                    ->state(
                        fn ($record) =>
                            ChatConversation::query()
                                ->where(
                                    'website_id',
                                    $this->websiteId
                                )
                                ->where(
                                    'visitor_id',
                                    $record->id
                                )
                                ->count()
                    ),

                TextColumn::make('last_activity_at')
                    ->label('Last Activity')
                    ->since()
                    ->tooltip(
    fn ($record) =>
        $record->last_activity_at
            ? BrowserTime::format(
                $record->last_activity_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)
                    ->sortable(),

            ])

            ->recordUrl(
                fn ($record) =>
                    ChatMessageResource::getUrl(
                        'visitor-conversations',
                        [
                            'website' => $this->websiteId,
                            'visitor' => $record->id,
                        ]
                    )
            )

            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            ChatMessageResource::getUrl(
                                'visitor-conversations',
                                [
                                    'website' => $this->websiteId,
                                    'visitor' => $record->id,
                                ]
                            )
                    ),
            ])

            ->defaultSort(
                'last_activity_at',
                'desc'
            );
    }
}