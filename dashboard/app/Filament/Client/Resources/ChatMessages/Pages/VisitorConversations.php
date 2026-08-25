<?php

namespace App\Filament\Client\Resources\ChatMessages\Pages;

use App\Filament\Client\Resources\ChatMessages\ChatMessageResource;
use App\Models\ChatConversation;
use App\Models\Visitor;
use App\Support\BrowserTime;
use App\Models\Website;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorConversations extends ListRecords
{
    protected static string $resource = ChatMessageResource::class;

    public int $websiteId;

    public int $visitorId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        $this->visitorId = (int) request()->route('visitor');

        // Verify website + visitor access once.
        $this->getWebsite();
        $this->getVisitor();

        parent::mount();
    }

    protected function getWebsite(): Website
    {
        return Website::query()
            ->whereKey($this->websiteId)
            ->where(
                'company_id',
                auth()->user()->company_id
            )
            ->firstOrFail();
    }

    protected function getVisitor(): Visitor
    {
        return Visitor::query()
            ->whereKey($this->visitorId)
            ->where(
                'website_id',
                $this->websiteId
            )
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        $uuid = $this->getVisitor()->visitor_uuid;

        return 'Visitor '
            . substr($uuid, 0, 8)
            . ' Conversations';
    }

    protected function getTableQuery(): Builder
    {
        return ChatConversation::query()
            ->where(
                'website_id',
                $this->websiteId
            )
            ->where(
                'visitor_id',
                $this->visitorId
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([

                TextColumn::make('id')
                    ->label('Conversation')
                    ->formatStateUsing(
                        fn ($state) =>
                            'Conversation #' . $state
                    )
                    ->sortable(),

                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Started')
                    ->date('d M Y')
                    ->tooltip(
    fn ($record) =>
        $record->started_at
            ? BrowserTime::format(
                $record->started_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->since()
                   ->tooltip(
    fn ($record) =>
        $record->updated_at
            ? BrowserTime::format(
                $record->updated_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)
                    ->sortable(),

            ])

            // Conversation rows फिलहाल clickable nahi.
            ->recordUrl(null)

            ->recordActions([])

            ->defaultSort(
                'updated_at',
                'desc'
            );
    }
}