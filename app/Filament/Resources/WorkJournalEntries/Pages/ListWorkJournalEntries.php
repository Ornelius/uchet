<?php

namespace App\Filament\Resources\WorkJournalEntries\Pages;

use App\Filament\Resources\WorkJournalEntries\WorkJournalEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkJournalEntries extends ListRecords
{
    protected static string $resource = WorkJournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
