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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('lat')
                    ->numeric()
                    ->required(),
                TextInput::make('lng')
                    ->numeric()
                    ->required(),
                TextInput::make('distance_m')
                    ->numeric()
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'valid' => 'Valid',
                        'rejected' => 'Ditolak',
                    ])
                    ->required()
                    ->default('pending'),
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
                TextColumn::make('id')->searchable(),
                TextColumn::make('event.title')->label('Kegiatan'),
                TextColumn::make('user.name')->label('Pengguna'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime(),
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
            'index' => Pages\ListAttendanceClaims::route('/'),
            'create' => Pages\CreateAttendanceClaim::route('/create'),
            'edit' => Pages\EditAttendanceClaim::route('/{record}/edit'),
        ];
    }
}
