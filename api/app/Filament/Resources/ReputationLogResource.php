<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReputationLogResource\Pages;
use App\Models\ReputationLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReputationLogResource extends Resource
{
    protected static ?string $model = ReputationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Reputasi Anggota';

    protected static ?string $modelLabel = 'Log Reputasi';

    protected static ?string $pluralModelLabel = 'Log Reputasi';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Anggota')->searchable(),
                TextColumn::make('action')->label('Aksi')->badge(),
                TextColumn::make('points')->label('Poin')->numeric()->sortable(),
                TextColumn::make('description')->label('Keterangan')->wrap(),
                TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReputationLogs::route('/'),
        ];
    }
}
