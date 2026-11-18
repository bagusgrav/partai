<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicAgencyScorecardResource\Pages;
use App\Models\PublicAgencyScorecard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PublicAgencyScorecardResource extends Resource
{
    protected static ?string $model = PublicAgencyScorecard::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Pengawasan Birokrasi';

    protected static ?string $navigationLabel = 'Rapor Instansi';

    protected static ?string $modelLabel = 'Rapor Instansi';

    protected static ?string $pluralModelLabel = 'Rapor Instansi';

    protected static ?string $slug = 'rapor-instansi';

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
            TextInput::make('agency_code')->required()->maxLength(255),
            TextInput::make('agency_name')->required()->maxLength(255),
            Select::make('agency_category')->options([
                'kementerian_lembaga' => 'Kementerian/Lembaga',
                'pemerintah_provinsi' => 'Pemerintah Provinsi',
                'pemerintah_kab_kota' => 'Pemerintah Kabupaten/Kota',
                'pelayanan_publik' => 'Pelayanan Publik',
            ])->required(),
            TextInput::make('province_name')->nullable(),
            TextInput::make('region_code')->nullable(),
            TextInput::make('period_year')->numeric()->required()->minValue(1900)->maxValue(2100),
            TextInput::make('rb_index_score')->numeric()->required()->minValue(0)->maxValue(100),
            Select::make('sakip_grade')->options(array_combine(['AA', 'A', 'BB', 'B', 'CC', 'C', 'D'], ['AA', 'A', 'BB', 'B', 'CC', 'C', 'D']))->required(),
            TextInput::make('merit_system_score')->numeric()->required()->minValue(0)->maxValue(400),
            TextInput::make('ombudsman_compliance_score')->numeric()->required()->minValue(0)->maxValue(100),
            TextInput::make('source_citation')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agency_code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('agency_name')->label('Instansi')->searchable()->sortable()->wrap(),
                TextColumn::make('performance_tier')->label('Tier')->badge()->color(fn (string $state): string => match ($state) {
                    'sangat_baik' => 'success',
                    'baik' => 'info',
                    'perlu_perbaikan' => 'warning',
                    default => 'danger',
                })->sortable(),
                TextColumn::make('bureaucracy_integrity_score')->label('Skor')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('rb_index_score')->label('RB')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('sakip_grade')->label('SAKIP')->badge(),
                TextColumn::make('merit_system_score')->label('Merit BKN')->numeric(decimalPlaces: 2),
                TextColumn::make('ombudsman_compliance_score')->label('Ombudsman')->numeric(decimalPlaces: 2),
                TextColumn::make('source_citation')->label('Sitasi')->wrap()->limit(80),
            ])
            ->actions([Tables\Actions\EditAction::make()->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false)])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicAgencyScorecards::route('/'),
            'create' => Pages\CreatePublicAgencyScorecard::route('/create'),
            'edit' => Pages\EditPublicAgencyScorecard::route('/{record}/edit'),
        ];
    }
}
