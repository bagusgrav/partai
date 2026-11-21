<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartySettingResource\Pages;
use App\Models\PartySetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PartySettingResource extends Resource
{
    protected static ?string $model = PartySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Tata Kelola';

    protected static ?string $navigationLabel = 'Branding Partai';

    protected static ?string $modelLabel = 'Pengaturan Partai';

    protected static ?string $pluralModelLabel = 'Pengaturan Partai';

    protected static ?string $slug = 'party-settings';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->hasRole('super_admin') ?? false)
            && PartySetting::query()->doesntExist();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('party_name')
                    ->label('Nama Partai')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo_path')
                    ->label('Logo Partai')
                    ->image()
                    ->disk('public')
                    ->directory('party-logos')
                    ->visibility('public')
                    ->maxSize(2048),
                TextInput::make('primary_color')
                    ->label('Warna Utama (HEX)')
                    ->required()
                    ->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->maxLength(7),
                TextInput::make('secondary_color')
                    ->label('Warna Sekunder (HEX)')
                    ->required()
                    ->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->maxLength(7),
                TextInput::make('tagline')
                    ->label('Tagline')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('party_name')->label('Nama Partai'),
                TextColumn::make('primary_color')->label('Warna Utama'),
                TextColumn::make('secondary_color')->label('Warna Sekunder'),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartySettings::route('/'),
            'create' => Pages\CreatePartySetting::route('/create'),
            'edit' => Pages\EditPartySetting::route('/{record}/edit'),
        ];
    }
}
