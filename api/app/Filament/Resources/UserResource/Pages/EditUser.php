<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $nikVerified = (bool) ($data['nik_verified'] ?? false);
        unset($data['nik_verified']);

        $data['nik_verified_at'] = $nikVerified && $this->record->nik !== null
            ? ($this->record->nik_verified_at ?? now())
            : null;

        return $data;
    }
}
