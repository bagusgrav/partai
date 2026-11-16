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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('claim_id')
                    ->relationship('claim', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('attester_id')
                    ->relationship('attester', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cluster_id')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'trusted' => 'Terpercaya',
                        'flagged' => 'Ditandai',
                    ])
                    ->required()
                    ->default('trusted'),
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
                TextColumn::make('claim_id'),
                TextColumn::make('attester.name')->label('Pemberi Attestasi'),
                TextColumn::make('cluster_id'),
                TextColumn::make('status')->badge(),
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
            'index' => Pages\ListAttestations::route('/'),
            'create' => Pages\CreateAttestation::route('/create'),
            'edit' => Pages\EditAttestation::route('/{record}/edit'),
        ];
    }
}
