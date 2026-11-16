<?php

namespace App\Filament\Resources\EssaySubmissionResource\Pages;

use App\Filament\Resources\EssaySubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEssaySubmissions extends ListRecords
{
    protected static string $resource = EssaySubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
