<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                Select::make('domain')
                    ->options([
                        'hak-konstitusional' => 'hak-konstitusional',
                        'alasan-bernegara' => 'alasan-bernegara',
                        'keuangan-negara' => 'keuangan-negara',
                        'hukum' => 'hukum',
                        'politik-bernegara' => 'politik-bernegara',
                    ])
                    ->required(),
                Select::make('level')
                    ->options([
                        'kenali' => 'kenali',
                        'pahami' => 'pahami',
                        'terapkan' => 'terapkan',
                        'kritisi' => 'kritisi',
                    ])
                    ->required(),
                Select::make('payload.type')
                    ->label('Jenis Soal')
                    ->options([
                        'multiple-choice' => 'Pilihan Ganda',
                        'essay' => 'Esai',
                    ])
                    ->required(),
                TextInput::make('payload.question')
                    ->label('Pertanyaan')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('payload.explanation')
                    ->label('Penjelasan')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('payload.instructions')
                    ->label('Instruksi Esai')
                    ->columnSpanFull(),
                Repeater::make('payload.options')
                    ->label('Pilihan Ganda')
                    ->simple(TextInput::make('option')->required())
                    ->columnSpanFull(),
                TextInput::make('payload.answer')
                    ->label('Kunci Jawaban'),
                TextInput::make('payload.sources.0.citation')
                    ->label('Sumber Hukum')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('domain'),
                TextColumn::make('level'),
                TextColumn::make('updated_at')->dateTime(),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
