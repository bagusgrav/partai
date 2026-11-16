<?php

namespace App\Filament\Resources\AttendanceClaimResource\Pages;

use App\Filament\Resources\AttendanceClaimResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceClaims extends ListRecords
{
    protected static string $resource = AttendanceClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
