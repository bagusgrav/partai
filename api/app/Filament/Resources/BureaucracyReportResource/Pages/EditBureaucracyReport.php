<?php

namespace App\Filament\Resources\BureaucracyReportResource\Pages;

use App\Filament\Resources\BureaucracyReportResource;
use App\Services\BureaucracyWatchService;
use Filament\Resources\Pages\EditRecord;

class EditBureaucracyReport extends EditRecord
{
    protected static string $resource = BureaucracyReportResource::class;

    private string $oldStatus;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldStatus = $this->record->verification_status;
        $data['verified_by'] = auth()->id();
        $data['verified_at'] = now();

        return $data;
    }

    protected function afterSave(): void
    {
        app(BureaucracyWatchService::class)->changeVerificationStatus(
            $this->record,
            $this->record->verification_status,
            [
                'public_resolution_note' => $this->record->public_resolution_note,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ],
            $this->oldStatus,
        );
    }
}
