<?php

namespace App\Filament\Resources\WelfareDataResource\Pages;

use App\Filament\Resources\WelfareDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWelfareData extends EditRecord
{
    protected static string $resource = WelfareDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
