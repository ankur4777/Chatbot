<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\ChatbotLead;
use App\Support\BrowserTime;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfflineRequests extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Missed Chats';

    protected static ?string $navigationLabel = 'Missed Chats';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 65;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.client.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unknown'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('notes')
                    ->label('Message')
                    ->limit(70)
                    ->wrap()
                    ->placeholder('No message'),

                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->created_at
                                ? BrowserTime::format(
                                    $record->created_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->recordActions([])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        return ChatbotLead::query()
            ->with(['website', 'conversation'])
            ->where('source', 'live_chat_offline_request')
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->clientCompanyId()
                )
            );
    }
}
