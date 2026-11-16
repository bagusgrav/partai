<?php

namespace App\Filament\Resources\EssaySubmissionResource\Pages;

use App\Filament\Resources\EssaySubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEssaySubmission extends EditRecord
{
    protected static string $resource = EssaySubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
