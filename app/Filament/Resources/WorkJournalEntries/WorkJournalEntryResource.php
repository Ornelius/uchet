<?php

namespace App\Filament\Resources\WorkJournalEntries;

use App\Filament\Resources\WorkJournalEntries\Pages\CreateWorkJournalEntry;
use App\Filament\Resources\WorkJournalEntries\Pages\EditWorkJournalEntry;
use App\Filament\Resources\WorkJournalEntries\Pages\ListWorkJournalEntries;
use App\Filament\Resources\WorkJournalEntries\Schemas\WorkJournalEntryForm;
use App\Filament\Resources\WorkJournalEntries\Tables\WorkJournalEntriesTable;
use App\Models\WorkJournalEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkJournalEntryResource extends Resource
{
    protected static ?string $model = WorkJournalEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $modelLabel = 'запись журнала';

    protected static ?string $pluralModelLabel = 'журнал работ';

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return WorkJournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkJournalEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkJournalEntries::route('/'),
            'create' => CreateWorkJournalEntry::route('/create'),
            'edit' => EditWorkJournalEntry::route('/{record}/edit'),
        ];
    }
}
