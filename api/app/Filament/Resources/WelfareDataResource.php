<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WelfareDataResource\Pages;
use App\Models\WelfareData;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WelfareDataResource extends Resource
{
    protected static ?string $model = WelfareData::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Pengawasan Data';

    protected static ?string $navigationLabel = 'Data Kesejahteraan';

    protected static ?string $modelLabel = 'Data Kesejahteraan';

    protected static ?string $pluralModelLabel = 'Data Kesejahteraan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('region_code')
                    ->label('Kode Wilayah')
                    ->required()
                    ->maxLength(255),
                TextInput::make('region_name')
                    ->label('Nama Wilayah')
                    ->required()
                    ->maxLength(255),
                Select::make('indicator')
                    ->label('Indikator')
                    ->options([
                        'kemiskinan' => 'Kemiskinan',
                        'stunting' => 'Stunting',
                        'bencana' => 'Bencana',
                    ])
                    ->required(),
                TextInput::make('value')
                    ->label('Nilai')
                    ->numeric()
                    ->required(),
                TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('year')
                    ->label('Tahun')
                    ->integer()
                    ->required(),
                Select::make('source')
                    ->label('Sumber Data')
                    ->options([
                        'BPS' => 'BPS',
                        'BNPB' => 'BNPB',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region_code')->label('Kode Wilayah')->searchable()->sortable(),
                TextColumn::make('region_name')->label('Wilayah')->searchable()->sortable(),
                TextColumn::make('indicator')->label('Indikator')->badge()->sortable(),
                TextColumn::make('value')->label('Nilai')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('unit')->label('Satuan'),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('source')->label('Sumber')->badge()->sortable(),
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
            'index' => Pages\ListWelfareData::route('/'),
            'create' => Pages\CreateWelfareData::route('/create'),
            'edit' => Pages\EditWelfareData::route('/{record}/edit'),
        ];
    }
}
