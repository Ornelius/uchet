<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Widgets\EquipmentStatsOverview;
use App\Models\Equipment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadQr')
                ->label('Скачать QR-код')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (Equipment $record) {
                    $png = $record->qr_png_path;

                    if ($png && file_exists($png)) {
                        return response()->download($png, 'qr-equipment-'.$record->id.'.png');
                    }
                })
                ->visible(fn (Equipment $record): bool => (bool) $record->qr_code),
            Action::make('regenerateQr')
                ->label('Перегенерировать QR')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Перегенерировать QR-код?')
                ->modalDescription('Старый QR-код будет заменён новым. Старые отпечатки перестанут открывать страницу.')
                ->action(function (Equipment $record): void {
                    $record->generateQrCode();

                    Notification::make()
                        ->success()
                        ->title('QR-код обновлён')
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EquipmentStatsOverview::class,
        ];
    }
}
