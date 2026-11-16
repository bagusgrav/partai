<?php

namespace App\Filament\Resources\FactionContractResource\Pages;

use App\Filament\Resources\FactionContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactionContracts extends ListRecords
{
    protected static string $resource = FactionContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Janji'),
        ];
    }
}
