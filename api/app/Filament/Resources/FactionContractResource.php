<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactionContractResource\Pages;
use App\Models\FactionContract;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FactionContractResource extends Resource
{
    protected static ?string $model = FactionContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Tata Kelola';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Kontrak Faksi';

    protected static ?string $modelLabel = 'Kontrak Faksi';

    protected static ?string $pluralModelLabel = 'Kontrak Faksi';

    protected static ?string $slug = 'kontrak-faksi';

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
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('politician_id')
                    ->label('Akun Politisi (Opsional)')
                    ->relationship('politician', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('politician_name')
                    ->label('Nama Politisi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->label('Judul Janji')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Detail Janji')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('region_code')
                    ->label('Kode BPS Wilayah (Opsional)')
                    ->regex('/^\d{4}$/')
                    ->maxLength(4),
                TextInput::make('province_name')
                    ->label('Provinsi (Opsional)')
                    ->maxLength(255),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'BELUM_DIMULAI' => 'Belum Dimulai',
                        'SEDANG_BERJALAN' => 'Sedang Berjalan',
                        'TERPENUHI' => 'Terpenuhi',
                        'GAGAL' => 'Gagal',
                    ])
                    ->required()
                    ->default('BELUM_DIMULAI'),
                TextInput::make('evidence_url')
                    ->label('URL Bukti')
                    ->url()
                    ->maxLength(255),
                DatePicker::make('deadline')
                    ->label('Tenggat Waktu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('politician_name')->label('Politisi')->searchable(),
                TextColumn::make('title')->label('Janji')->searchable()->wrap(),
                TextColumn::make('region_code')->label('Kode BPS')->searchable(),
                TextColumn::make('province_name')->label('Provinsi')->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'TERPENUHI' => 'success',
                        'GAGAL' => 'danger',
                        'SEDANG_BERJALAN' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('deadline')->label('Tenggat')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactionContracts::route('/'),
            'create' => Pages\CreateFactionContract::route('/create'),
            'edit' => Pages\EditFactionContract::route('/{record}/edit'),
        ];
    }
}
