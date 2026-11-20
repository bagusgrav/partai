<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionWelfareIndexResource\Pages;
use App\Models\RegionWelfareIndex;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RegionWelfareIndexResource extends Resource
{
    protected static ?string $model = RegionWelfareIndex::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Keuangan & Pengawasan';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Indeks Kerentanan (IKK)';

    protected static ?string $modelLabel = 'Indeks Kerentanan (IKK)';

    protected static ?string $pluralModelLabel = 'Indeks Kerentanan (IKK)';

    protected static ?string $slug = 'indeks-kerentanan-ikk';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus', 'panelis']) ?? false;
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
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('region_code')->label('Kode BPS')->required()->regex('/^\d{4}$/')->maxLength(4),
            TextInput::make('region_name')->label('Kabupaten/Kota')->required()->maxLength(255),
            TextInput::make('province_name')->label('Provinsi')->required()->maxLength(255),
            TextInput::make('period_year')->label('Tahun')->required()->integer()->minValue(1900)->maxValue(2100),
            TextInput::make('poverty_rate')->label('Kemiskinan (%)')->required()->numeric()->minValue(0)->maxValue(100),
            TextInput::make('stunting_rate')->label('Stunting (%)')->required()->numeric()->minValue(0)->maxValue(100),
            TextInput::make('gini_ratio')->label('Rasio Gini')->required()->numeric()->minValue(0)->maxValue(1),
            TextInput::make('open_unemployment_rate')->label('TPT (%)')->required()->numeric()->minValue(0)->maxValue(100),
            TextInput::make('disaster_events_count')->label('Kejadian bencana')->required()->integer()->minValue(0),
            TextInput::make('affected_population')->label('Penduduk terdampak')->required()->integer()->minValue(0),
            TextInput::make('disaster_risk_score')->label('Skor risiko BNPB')->required()->numeric()->minValue(0)->maxValue(100),
            TextInput::make('bps_source_citation')->label('Sitasi BPS')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('bnpb_source_citation')->label('Sitasi BNPB')->required()->maxLength(255)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region_code')->label('Kode BPS')->searchable()->sortable(),
                TextColumn::make('region_name')->label('Kabupaten/Kota')->searchable()->sortable(),
                TextColumn::make('province_name')->label('Provinsi')->searchable()->sortable(),
                TextColumn::make('period_year')->label('Tahun')->sortable(),
                TextColumn::make('ikk_score')->label('Skor IKK')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('vulnerability_tier')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kritis' => 'danger',
                        'tinggi' => 'warning',
                        'sedang' => 'info',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('poverty_rate')->label('Kemiskinan')->suffix('%'),
                TextColumn::make('stunting_rate')->label('Stunting')->suffix('%'),
                TextColumn::make('gini_ratio')->label('Gini')->numeric(decimalPlaces: 3),
                TextColumn::make('open_unemployment_rate')->label('TPT')->suffix('%'),
                TextColumn::make('disaster_risk_score')->label('Risiko bencana')->suffix('/100'),
                TextColumn::make('bps_source_citation')->label('Sumber BPS')->wrap()->limit(60),
                TextColumn::make('bnpb_source_citation')->label('Sumber BNPB')->wrap()->limit(60),
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
            'index' => Pages\ListRegionWelfareIndices::route('/'),
            'create' => Pages\CreateRegionWelfareIndex::route('/create'),
            'edit' => Pages\EditRegionWelfareIndex::route('/{record}/edit'),
        ];
    }
}
