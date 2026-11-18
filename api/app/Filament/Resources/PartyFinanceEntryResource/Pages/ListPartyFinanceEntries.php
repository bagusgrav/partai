<?php

namespace App\Filament\Resources\PartyFinanceEntryResource\Pages;

use App\Filament\Resources\PartyFinanceEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartyFinanceEntries extends ListRecords
{
    protected static string $resource = PartyFinanceEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
