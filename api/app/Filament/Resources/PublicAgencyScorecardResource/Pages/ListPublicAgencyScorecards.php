<?php

namespace App\Filament\Resources\PublicAgencyScorecardResource\Pages;

use App\Filament\Resources\PublicAgencyScorecardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPublicAgencyScorecards extends ListRecords
{
    protected static string $resource = PublicAgencyScorecardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(fn (): bool => PublicAgencyScorecardResource::canCreate()),
        ];
    }
}
