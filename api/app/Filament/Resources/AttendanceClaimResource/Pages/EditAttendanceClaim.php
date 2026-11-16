<?php

namespace App\Filament\Resources\AttendanceClaimResource\Pages;

use App\Filament\Resources\AttendanceClaimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceClaim extends EditRecord
{
    protected static string $resource = AttendanceClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
