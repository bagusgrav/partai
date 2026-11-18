<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationSeatResource\Pages;
use App\Models\OrganizationSeat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrganizationSeatResource extends Resource
{
    protected static ?string $model = OrganizationSeat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Tata Kelola';

    protected static ?string $navigationLabel = 'Kursi Organisasi';

    protected static ?string $modelLabel = 'Kursi Organisasi';

    protected static ?string $pluralModelLabel = 'Kursi Organisasi';

    protected static ?string $slug = 'kursi-organisasi';

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
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('seat_code')->required()->maxLength(255),
            TextInput::make('title')->required()->maxLength(255),
            Select::make('layer')->options([
                'anggota_berdaulat' => 'Anggota Berdaulat',
                'pengurus_legal' => 'Pengurus Legal',
                'peran_fungsional_sortisi' => 'Fungsional Sortisi',
                'perwalian_teknis' => 'Perwalian Teknis',
                'cluster_teritorial' => 'Cluster Teritorial',
            ])->required(),
            Select::make('territory_level')->options([
                'nasional' => 'Nasional',
                'provinsi' => 'Provinsi',
                'kab_kota' => 'Kabupaten/Kota',
                'kecamatan_cluster' => 'Kecamatan/Cluster',
            ])->required(),
            Textarea::make('answering_scope')->required()->columnSpanFull(),
            Select::make('selection_method')->options([
                'sortisi_acak' => 'Sortisi Acak',
                'pemilihan_anggota' => 'Pemilihan Anggota',
                'mandat_legal_terbatas' => 'Mandat Legal Terbatas',
                'perwalian_multisig' => 'Perwalian Multisig',
            ])->required(),
            TextInput::make('required_mastery_level')->numeric()->minValue(1)->maxValue(4)->required(),
            TextInput::make('required_domain')->nullable(),
            TextInput::make('required_min_participation')->numeric()->minValue(0)->required(),
            TextInput::make('holder_display_name')->nullable(),
            Select::make('holder_user_id')->relationship('holder', 'name')->searchable()->nullable(),
            DatePicker::make('term_starts_at'),
            DatePicker::make('term_ends_at'),
            Select::make('status')->options([
                'open' => 'Open',
                'filled' => 'Filled',
                'sortition_pool' => 'Sortition Pool',
                'term_expiring' => 'Term Expiring',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('seat_code')->searchable()->sortable(),
            TextColumn::make('title')->searchable()->wrap(),
            TextColumn::make('layer')->badge(),
            TextColumn::make('status')->badge(),
            TextColumn::make('required_mastery_level')->label('Mastery'),
            TextColumn::make('holder_display_name')->label('Pemegang'),
            TextColumn::make('term_ends_at')->label('Sunset')->date(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizationSeats::route('/'),
            'create' => Pages\CreateOrganizationSeat::route('/create'),
            'edit' => Pages\EditOrganizationSeat::route('/{record}/edit'),
        ];
    }
}
