<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IdentityProposalResource\Pages;
use App\Models\IdentityProposal;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class IdentityProposalResource extends Resource
{
    protected static ?string $model = IdentityProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Tata Kelola';

    protected static ?string $navigationLabel = 'Usulan Identitas';

    protected static ?string $modelLabel = 'Usulan Identitas';

    protected static ?string $pluralModelLabel = 'Usulan Identitas';

    protected static ?string $slug = 'identity-proposals';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return self::canViewAny();
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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('user.name')->label('Pengusul')->disabled(),
            TextInput::make('proposed_name')->label('Nama Partai')->disabled(),
            TextInput::make('proposed_color')->label('Warna Utama')->disabled(),
            Textarea::make('proposed_logo_desc')
                ->label('Deskripsi Logo')
                ->rows(5)
                ->disabled()
                ->columnSpanFull(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proposed_name')->label('Nama Partai')->searchable(),
                TextColumn::make('proposed_color')->label('Warna Utama'),
                TextColumn::make('proposed_logo_desc')->label('Deskripsi Logo')->wrap()->limit(100),
                TextColumn::make('user.name')->label('Pengusul'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Diajukan')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ]),
            ])
            ->actions([
                ViewAction::make()->label('Lihat'),
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->visible(fn (IdentityProposal $record): bool => $record->status === 'pending')
                    ->action(fn (IdentityProposal $record) => $record->update(['status' => 'approved'])),
                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->visible(fn (IdentityProposal $record): bool => $record->status === 'pending')
                    ->action(fn (IdentityProposal $record) => $record->update(['status' => 'rejected'])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdentityProposals::route('/'),
        ];
    }
}
