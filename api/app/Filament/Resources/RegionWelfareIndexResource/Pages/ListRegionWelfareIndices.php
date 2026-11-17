<?php

namespace App\Filament\Resources\RegionWelfareIndexResource\Pages;

use App\Filament\Resources\RegionWelfareIndexResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListRegionWelfareIndices extends ListRecords
{
    protected static string $resource = RegionWelfareIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah data IKK'),
            Actions\Action::make('syncOpenData')
                ->label('Sinkronkan data terbuka')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => RegionWelfareIndexResource::canCreate())
                ->requiresConfirmation()
                ->action(function (): void {
                    Artisan::call('welfare:sync-open-data');
                    Notification::make()
                        ->title('Sinkronisasi IKK selesai')
                        ->body(trim(Artisan::output()))
                        ->success()
                        ->send();
                }),
        ];
    }
}
