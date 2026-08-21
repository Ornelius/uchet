<?php

namespace App\Filament\Resources\WorkJournalEntries\Pages;

use App\Filament\Resources\WorkJournalEntries\WorkJournalEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkJournalEntry extends EditRecord
{
    protected static string $resource = WorkJournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
