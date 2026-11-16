<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EssaySubmissionResource\Pages;
use App\Models\EssaySubmission;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EssaySubmissionResource extends Resource
{
    protected static ?string $model = EssaySubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('item_id')
                    ->relationship('item', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('answer_text')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'reviewed' => 'Ditinjau',
                    ])
                    ->required()
                    ->default('pending'),
                TextInput::make('request_id')
                    ->required()
                    ->default(fn (): string => (string) Str::uuid())
                    ->rule('uuid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('user.name')->label('Pengguna'),
                TextColumn::make('item_id')->label('ID Materi'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEssaySubmissions::route('/'),
            'create' => Pages\CreateEssaySubmission::route('/create'),
            'edit' => Pages\EditEssaySubmission::route('/{record}/edit'),
        ];
    }
}
