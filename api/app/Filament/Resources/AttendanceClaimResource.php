<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceClaimResource\Pages;
use App\Models\AttendanceClaim;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AttendanceClaimResource extends Resource
{
    protected static ?string $model = AttendanceClaim::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Aksi Lapangan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Klaim Kehadiran';

    protected static ?string $modelLabel = 'Klaim Kehadiran';

    protected static ?string $pluralModelLabel = 'Klaim Kehadiran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('event_id')
                    ->label('Aksi Lapangan')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('Anggota')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('lat')
                    ->label('Lintang')
                    ->numeric()
                    ->required(),
                TextInput::make('lng')
                    ->label('Bujur')
                    ->numeric()
                    ->required(),
                TextInput::make('distance_m')
                    ->label('Jarak (meter)')
                    ->numeric()
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'valid' => 'Valid',
                        'rejected' => 'Ditolak',
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
                TextColumn::make('id')->label('ID')->searchable(),
                TextColumn::make('event.title')->label('Aksi Lapangan'),
                TextColumn::make('user.name')->label('Anggota'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Diajukan Pada')->dateTime(),
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
            'index' => Pages\ListAttendanceClaims::route('/'),
            'create' => Pages\CreateAttendanceClaim::route('/create'),
            'edit' => Pages\EditAttendanceClaim::route('/{record}/edit'),
        ];
    }
}
