<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Aksi Lapangan';

    protected static ?string $modelLabel = 'Aksi Lapangan';

    protected static ?string $pluralModelLabel = 'Aksi Lapangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('region_code')
                    ->label('Kode BPS Wilayah (Opsional)')
                    ->regex('/^\d{4}$/')
                    ->maxLength(4),
                TextInput::make('province_name')
                    ->label('Provinsi (Opsional)')
                    ->maxLength(255),
                TextInput::make('location_lat')
                    ->label('Lintang')
                    ->numeric()
                    ->required(),
                TextInput::make('location_lng')
                    ->label('Bujur')
                    ->numeric()
                    ->required(),
                TextInput::make('radius_m')
                    ->label('Radius (meter)')
                    ->numeric()
                    ->required()
                    ->default(100),
                DateTimePicker::make('starts_at')
                    ->label('Waktu Mulai')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Waktu Selesai')
                    ->required(),
                Select::make('coordinator_id')
                    ->label('Koordinator')
                    ->relationship('coordinator', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('active'),
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
                TextColumn::make('id')->label('ID')->searchable(),
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('region_code')->label('Kode BPS')->searchable(),
                TextColumn::make('province_name')->label('Provinsi')->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('starts_at')->label('Waktu Mulai')->dateTime(),
                TextColumn::make('ends_at')->label('Waktu Selesai')->dateTime(),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
