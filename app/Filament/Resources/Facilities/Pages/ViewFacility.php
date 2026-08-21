<?php

namespace App\Filament\Resources\Facilities\Pages;

use App\Filament\Resources\Facilities\FacilityResource;
use App\Filament\Resources\Facilities\Widgets\FacilityBreakdownsChart;
use App\Filament\Resources\Facilities\Widgets\FacilityEquipmentTypesChart;
use App\Filament\Resources\Facilities\Widgets\FacilitySchemeMap;
use App\Filament\Resources\Facilities\Widgets\FacilityStatsOverview;
use App\Filament\Resources\Facilities\Widgets\FacilityWorkOrdersChart;
use App\Models\Facility;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Livewire;
use Filament\Widgets\WidgetConfiguration;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViewFacility extends ViewRecord
{
    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Скачать PDF-паспорт')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn (Facility $record): BinaryFileResponse => $this->generatePassportPdf($record)),
            EditAction::make(),
        ];
    }

    public function generatePassportPdf(Facility $record): BinaryFileResponse
    {
        $record->load([
            'equipment' => fn ($query) => $query->with('serviceTasks'),
            'workOrders' => fn ($query) => $query->with('equipment')->latest('planned_date')->limit(15),
            'workJournalEntries' => fn ($query) => $query->with('equipment')->latest('work_date')->limit(15),
        ]);

        $tasks = $record->equipment->flatMap->serviceTasks
            ->sortBy(fn ($task) => $task->next_due_date ?? now()->endOfAllTime())
            ->values();

        $pdf = Pdf::loadView('facilities.passport-pdf', [
            'facility' => $record,
            'tasksSorted' => $tasks,
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'passport-').'.pdf';
        file_put_contents($filePath, $pdf->output());

        return response()->download($filePath, 'passport-'.$record->id.'-'.now()->format('Ymd-Hi').'.pdf')
            ->deleteFileAfterSend(true);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FacilityStatsOverview::class,
            FacilityWorkOrdersChart::class,
            FacilityBreakdownsChart::class,
            FacilitySchemeMap::class,
            FacilityEquipmentTypesChart::class,
        ];
    }

    public function getWidgetsSchemaComponents(array $widgets, array $data = []): array
    {
        return collect($widgets)
            ->values()
            ->filter(fn (string | WidgetConfiguration $widget): bool => $this->normalizeWidgetClass($widget)::canView())
            ->map(function (string | WidgetConfiguration $widget, int $widgetKey) use ($data): Livewire {
                $widgetClass = $this->normalizeWidgetClass($widget);

                $isSchemeMap = $widgetClass === FacilitySchemeMap::class;

                $component = Livewire::make(
                    $widgetClass,
                    fn (): array => [
                        ...$this->getWidgetData(),
                        ...$data,
                        ...(($widget instanceof WidgetConfiguration) ? [
                            ...$widget->widget::getDefaultProperties(),
                            ...$widget->getProperties(),
                        ] : $widget::getDefaultProperties()),
                        ...(property_exists($this, 'filters') ? ['pageFilters' => $this->filters] : []),
                    ],
                )->key("{$widgetClass}-{$widgetKey}");

                if ($isSchemeMap) {
                    $component->columnSpan('full');
                } else {
                    $component->liberatedFromContainerGrid();
                }

                return $component;
            })
            ->all();
    }
}