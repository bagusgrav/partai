<?php

namespace App\Filament\Resources\OrganizationSeatResource\Pages;

use App\Filament\Resources\OrganizationSeatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationSeats extends ListRecords
{
    protected static string $resource = OrganizationSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
