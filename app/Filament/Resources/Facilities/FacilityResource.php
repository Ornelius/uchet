<?php

namespace App\Filament\Resources\Facilities;

use App\Filament\Resources\Facilities\Pages\CreateFacility;
use App\Filament\Resources\Facilities\Pages\EditFacility;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Filament\Resources\Facilities\Pages\ViewFacility;
use App\Filament\Resources\Facilities\RelationManagers\EquipmentRelationManager;
use App\Filament\Resources\Facilities\RelationManagers\WorkJournalEntriesRelationManager;
use App\Filament\Resources\Facilities\RelationManagers\WorkOrdersRelationManager;
use App\Filament\Resources\Facilities\Schemas\FacilityForm;
use App\Filament\Resources\Facilities\Schemas\FacilityInfolist;
use App\Filament\Resources\Facilities\Tables\FacilitiesTable;
use App\Models\Facility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $modelLabel = 'объект';

    protected static ?string $pluralModelLabel = 'объекты';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return FacilityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FacilityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EquipmentRelationManager::class,
            WorkOrdersRelationManager::class,
            WorkJournalEntriesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\FacilityStatsOverview::class,
            Widgets\FacilityWorkOrdersChart::class,
            Widgets\FacilityBreakdownsChart::class,
            Widgets\FacilityEquipmentTypesChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'view' => ViewFacility::route('/{record}'),
            'edit' => EditFacility::route('/{record}/edit'),
        ];
    }
}
