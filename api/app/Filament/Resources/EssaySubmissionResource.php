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

    protected static ?string $navigationGroup = 'Pendidikan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Pengumpulan Esai';

    protected static ?string $modelLabel = 'Pengumpulan Esai';

    protected static ?string $pluralModelLabel = 'Pengumpulan Esai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Anggota')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('item_id')
                    ->label('Bank Soal')
                    ->relationship('item', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('answer_text')
                    ->label('Jawaban Esai')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'reviewed' => 'Ditinjau',
                    ])
                    ->required()
                    ->default('pending'),
                TextInput::make('request_id')
                    ->label('ID Permintaan')
                    ->required()
                    ->default(fn (): string => (string) Str::uuid())
                    ->rule('uuid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('user.name')->label('Pengguna'),
                TextColumn::make('item_id')->label('ID Materi'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Dikumpulkan Pada')->dateTime(),
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
            'index' => Pages\ListEssaySubmissions::route('/'),
            'create' => Pages\CreateEssaySubmission::route('/create'),
            'edit' => Pages\EditEssaySubmission::route('/{record}/edit'),
        ];
    }
}
