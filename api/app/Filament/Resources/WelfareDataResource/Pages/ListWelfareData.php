<?php

namespace App\Filament\Resources\WelfareDataResource\Pages;

use App\Filament\Resources\WelfareDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWelfareData extends ListRecords
{
    protected static string $resource = WelfareDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
