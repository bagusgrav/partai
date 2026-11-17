<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Bank Soal';

    protected static ?string $modelLabel = 'Bank Soal';

    protected static ?string $pluralModelLabel = 'Bank Soal';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->label('ID Soal')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                Select::make('domain')
                    ->label('Ranah')
                    ->options([
                        'hak-konstitusional' => 'Hak Konstitusional',
                        'alasan-bernegara' => 'Alasan Bernegara',
                        'keuangan-negara' => 'Keuangan Negara',
                        'hukum' => 'hukum',
                        'politik-bernegara' => 'Politik Bernegara',
                    ])
                    ->required(),
                Select::make('level')
                    ->label('Tingkatan')
                    ->options([
                        'kenali' => 'Kenali',
                        'pahami' => 'Pahami',
                        'terapkan' => 'Terapkan',
                        'kritisi' => 'Kritisi',
                    ])
                    ->required(),
                Select::make('payload.type')
                    ->label('Jenis Soal')
                    ->options([
                        'multiple-choice' => 'Pilihan Ganda',
                        'essay' => 'Esai',
                    ])
                    ->live()
                    ->required(),
                TextInput::make('payload.question')
                    ->label('Pertanyaan')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('payload.explanation')
                    ->label('Penjelasan')
                    ->rows(4)
                    ->visible(fn (Get $get): bool => $get('payload.type') !== 'essay')
                    ->columnSpanFull(),
                RichEditor::make('payload.explanation')
                    ->label('Kunci Jawaban / Pembahasan')
                    ->visible(fn (Get $get): bool => $get('payload.type') === 'essay')
                    ->columnSpanFull(),
                TextInput::make('payload.instructions')
                    ->label('Instruksi Esai')
                    ->columnSpanFull(),
                Repeater::make('payload.options')
                    ->label('Pilihan Jawaban')
                    ->simple(TextInput::make('option')->label('Pilihan')->required())
                    ->visible(fn (Get $get): bool => $get('payload.type') !== 'essay')
                    ->columnSpanFull(),
                TextInput::make('payload.answer')
                    ->label('Kunci Jawaban')
                    ->visible(fn (Get $get): bool => $get('payload.type') !== 'essay'),
                TextInput::make('payload.sources.0.citation')
                    ->label('Sumber Hukum')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID Soal'),
                TextColumn::make('domain')->label('Ranah'),
                TextColumn::make('level')->label('Tingkatan'),
                TextColumn::make('updated_at')->label('Diperbarui Pada')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
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
