<?php

namespace App\Filament\Client\Resources\ChatbotLeads\Pages;

use App\Filament\Client\Resources\ChatbotLeads\ChatbotLeadResource;
use App\Models\ChatbotLead;
use App\Models\Website;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageChatbotLeads extends ListRecords
{
    protected static string $resource = ChatbotLeadResource::class;

    public int $websiteId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        // Make sure logged-in owner is allowed
        // to access this website.
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
        return $this->getWebsite()->name . ' Leads';
    }

    protected function getTableQuery(): Builder
    {
        return ChatbotLead::query()
            ->where('website_id', $this->websiteId)
            ->whereHas('website', function ($query) {
                $query->where(
                    'company_id',
                    auth()->user()->company_id
                );
            });
    }

   public function table(Table $table): Table
{
    return $table
        ->query($this->getTableQuery())

        ->columns([

            TextColumn::make('visitor.visitor_uuid')
    ->label('Visitor')
    ->formatStateUsing(
        fn ($state) => $state
            ? 'Visitor ' . substr($state, 0, 8)
            : '—'
    )
    ->tooltip(
        fn ($record) => $record->visitor?->visitor_uuid
    )
    ->copyable()
    ->copyableState(
        fn ($record) => $record->visitor?->visitor_uuid
    )
    ->searchable(),

            TextColumn::make('conversation.id')
                ->label('Conversation')
                ->sortable()
                ->placeholder('—'),

            TextColumn::make('name')
                ->label('Full Name')
                ->searchable()
                ->sortable()
                ->placeholder('—'),

            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->copyable()
                ->placeholder('—'),

            TextColumn::make('phone')
                ->label('Phone')
                ->searchable()
                ->copyable()
                ->placeholder('—'),

            TextColumn::make('created_at')
                ->label('Created At')
                ->since()
                ->tooltip(
                    fn ($record) =>
                        $record->created_at
                            ? $record->created_at->format('d M Y, h:i A')
                            : 'N/A'
                )
                ->sortable(),
        ])
        ->recordUrl(null)

        ->recordActions([])

        ->defaultSort('created_at', 'desc');
}
}