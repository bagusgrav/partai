<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttestationResource\Pages;
use App\Models\Attestation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AttestationResource extends Resource
{
    protected static ?string $model = Attestation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Aksi Lapangan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Atestasi Silang';

    protected static ?string $modelLabel = 'Atestasi Silang';

    protected static ?string $pluralModelLabel = 'Atestasi Silang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('claim_id')
                    ->label('Klaim Kehadiran')
                    ->relationship('claim', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('attester_id')
                    ->label('Pemberi Atestasi')
                    ->relationship('attester', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cluster_id')
                    ->label('ID Klaster')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'trusted' => 'Terpercaya',
                        'flagged' => 'Ditandai',
                    ])
                    ->required()
                    ->default('trusted'),
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
                TextColumn::make('claim_id')->label('ID Klaim Kehadiran'),
                TextColumn::make('attester.name')->label('Pemberi Atestasi'),
                TextColumn::make('cluster_id')->label('ID Klaster'),
                TextColumn::make('status')->label('Status')->badge(),
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
            'index' => Pages\ListAttestations::route('/'),
            'create' => Pages\CreateAttestation::route('/create'),
            'edit' => Pages\EditAttestation::route('/{record}/edit'),
        ];
    }
}
