<?php

namespace App\Filament\Resources\CivicQuestionResource\Pages;

use App\Filament\Resources\CivicQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCivicQuestions extends ListRecords
{
    protected static string $resource = CivicQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Soal'),
        ];
    }
}
