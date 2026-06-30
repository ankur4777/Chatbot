<?php

namespace App\Filament\Resources\ChatbotLeads;

use App\Filament\Resources\ChatbotLeads\Pages\CreateChatbotLead;
use App\Filament\Resources\ChatbotLeads\Pages\EditChatbotLead;
use App\Filament\Resources\ChatbotLeads\Pages\ListChatbotLeads;
use App\Filament\Resources\ChatbotLeads\Schemas\ChatbotLeadForm;
use App\Filament\Resources\ChatbotLeads\Tables\ChatbotLeadsTable;
use App\Models\ChatbotLead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatbotLeadResource extends Resource
{
    protected static ?string $model = ChatbotLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ChatbotLeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotLeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotLeads::route('/'),
            'create' => CreateChatbotLead::route('/create'),
            'edit' => EditChatbotLead::route('/{record}/edit'),
        ];
    }
}
