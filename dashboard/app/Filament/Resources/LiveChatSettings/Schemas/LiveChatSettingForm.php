<?php

namespace App\Filament\Resources\LiveChatSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LiveChatSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Live Chat Availability')
                    ->schema([
                        Select::make('website_id')
                            ->label('Website')
                            ->relationship('website', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false),

                        Toggle::make('enable_live_chat')
                            ->label('Enable Live Chat')
                            ->live()
                            ->default(false),

                    ])
                    ->columns(1),

                Section::make('Operational Settings')
                    ->schema([
                        Select::make('offline_behavior')
                            ->label('When No Agent Is Online')
                            ->options([
                                'show_offline_form' => 'Show offline form',
                                'hide_button' => 'Hide the button',
                            ])
                            ->default('show_offline_form')
                            ->required()
                            ->disabled(
                                fn (Get $get): bool =>
                                    ! (bool) $get('enable_live_chat')
                            ),

                        TextInput::make('max_active_chats_per_agent')
                            ->label('Max Active Chats Per Agent')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(3)
                            ->required()
                            ->disabled(
                                fn (Get $get): bool =>
                                    ! (bool) $get('enable_live_chat')
                            ),

                        TextInput::make('max_agents_per_website')
                            ->label('Max Agents For This Website')
                            ->helperText('Only Super Admin can control how many agents the client may assign to this website. Leave empty for no limit.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500)
                            ->disabled(
                                fn (Get $get): bool =>
                                    ! (bool) $get('enable_live_chat')
                            ),

                        Textarea::make('waiting_message')
                            ->label('Waiting Message')
                            ->helperText('Shown after a visitor requests an agent while agents are online.')
                            ->rows(3)
                            ->disabled(
                                fn (Get $get): bool =>
                                    ! (bool) $get('enable_live_chat')
                            ),

                        Textarea::make('offline_message')
                            ->label('Offline Message')
                            ->helperText('Shown when no agent is online and the offline form is enabled.')
                            ->rows(3)
                            ->disabled(
                                fn (Get $get): bool =>
                                    ! (bool) $get('enable_live_chat')
                            ),
                    ])
                    ->columns(1),
            ]);
    }
}
