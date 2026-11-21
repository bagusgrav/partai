<?php

namespace App\Filament\Resources\PartySettingResource\Pages;

use App\Filament\Resources\PartySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartySettings extends ListRecords
{
    protected static string $resource = PartySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pengaturan'),
        ];
    }
}
