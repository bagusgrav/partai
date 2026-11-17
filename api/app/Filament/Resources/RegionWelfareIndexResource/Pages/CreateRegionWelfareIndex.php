<?php

namespace App\Filament\Resources\RegionWelfareIndexResource\Pages;

use App\Filament\Resources\RegionWelfareIndexResource;
use App\Services\WelfareIndexService;
use Filament\Resources\Pages\CreateRecord;

class CreateRegionWelfareIndex extends CreateRecord
{
    protected static string $resource = RegionWelfareIndexResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $calculation = app(WelfareIndexService::class)->calculate($data);

        return [
            ...$data,
            'ikk_score' => $calculation['ikk_score'],
            'vulnerability_tier' => $calculation['vulnerability_tier'],
        ];
    }
}
