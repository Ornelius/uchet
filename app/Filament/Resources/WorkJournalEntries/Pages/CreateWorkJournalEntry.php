<?php

namespace App\Filament\Resources\WorkJournalEntries\Pages;

use App\Filament\Resources\WorkJournalEntries\WorkJournalEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkJournalEntry extends CreateRecord
{
    protected static string $resource = WorkJournalEntryResource::class;
}
