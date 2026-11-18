<?php

namespace App\Filament\Resources\PublicAgencyScorecardResource\Pages;

use App\Filament\Resources\PublicAgencyScorecardResource;
use App\Services\BureaucracyWatchService;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicAgencyScorecard extends CreateRecord
{
    protected static string $resource = PublicAgencyScorecardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $calculation = app(BureaucracyWatchService::class)->calculateScore(
            (float) $data['rb_index_score'],
            (float) $data['merit_system_score'],
            (float) $data['ombudsman_compliance_score'],
        );

        return [...$data, ...$calculation];
    }
}
