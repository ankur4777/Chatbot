<?php

namespace App\Filament\Client\Resources\LiveChatSettings\Schemas;

use App\Models\WebsiteSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LiveChatSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('website_id')
                    ->label('Website')
                    ->relationship(
                        name: 'website',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) =>
                            $query->where(
                                'company_id',
                                auth()->user()->company_id
                            )
                    )
                    ->disabled()
                    ->dehydrated(false),

                Toggle::make('enable_live_chat')
                    ->label('Enable Live Chat')
                    ->helperText('Controlled by Super Admin.')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('offline_behavior')
                    ->label('When No Agent Is Online')
                    ->options([
                        'show_offline_form' => 'Show offline form',
                        'hide_button' => 'Hide the button',
                    ])
                    ->default('show_offline_form')
                    ->required()
                    ->disabled(
                        fn (?WebsiteSetting $record): bool =>
                            ! (bool) $record?->enable_live_chat
                    ),

                TextInput::make('max_active_chats_per_agent')
                    ->label('Max Active Chats Per Agent')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->default(3)
                    ->required()
                    ->disabled(
                        fn (?WebsiteSetting $record): bool =>
                            ! (bool) $record?->enable_live_chat
                    ),

                Textarea::make('waiting_message')
                    ->label('Waiting Message')
                    ->helperText('Shown after a visitor requests an agent while agents are online.')
                    ->rows(3)
                    ->disabled(
                        fn (?WebsiteSetting $record): bool =>
                            ! (bool) $record?->enable_live_chat
                    ),

                Textarea::make('offline_message')
                    ->label('Offline Message')
                    ->helperText('Shown when no agent is online and the offline form is enabled.')
                    ->rows(3)
                    ->disabled(
                        fn (?WebsiteSetting $record): bool =>
                            ! (bool) $record?->enable_live_chat
                    ),
            ]);
    }
}
