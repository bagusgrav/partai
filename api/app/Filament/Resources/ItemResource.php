<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
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
                Textarea::make('payload')
                    ->required()
                    ->rows(20)
                    ->rules(['json'])
                    ->formatStateUsing(
                        fn (mixed $state): string => $state === null
                            ? ''
                            : (is_string($state)
                                ? $state
                                : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
                    )
                    ->dehydrateStateUsing(
                        fn (string $state): mixed => json_decode($state, true, flags: JSON_THROW_ON_ERROR),
                    ),
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
