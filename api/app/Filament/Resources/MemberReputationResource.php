<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberReputationResource\Pages;
use App\Models\MemberReputation;
use App\Services\ReputationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MemberReputationResource extends Resource
{
    protected static ?string $model = MemberReputation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Tata Kelola';

    protected static ?string $navigationLabel = 'Skor Reputasi';

    protected static ?string $modelLabel = 'Reputasi Anggota';

    protected static ?string $pluralModelLabel = 'Reputasi Anggota';

    protected static ?string $slug = 'reputasi-anggota';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus', 'panelis']) ?? false;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Anggota')->searchable(),
                TextColumn::make('overall_mastery_level')->label('Level Mastery')->sortable(),
                TextColumn::make('participation_score')->label('Partisipasi')->numeric()->sortable(),
                TextColumn::make('overall_mastery_level')
                    ->label('Layak')
                    ->formatStateUsing(fn (int $state): string => $state >= 4
                        ? 'Panel Audit, Panel Etik, Bakal Caleg'
                        : 'Belum memenuhi ambang'),
            ])
            ->actions([
                Action::make('correction')
                    ->label('Catat Koreksi/Audit')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'panelis']) ?? false)
                    ->form([
                        Select::make('score_type')
                            ->label('Jenis skor')
                            ->options([
                                'mastery' => 'Mastery Score',
                                'participation' => 'Participation Score',
                            ])
                            ->required()
                            ->live(),
                        Select::make('domain')
                            ->label('Domain mastery')
                            ->options(array_combine(
                                ReputationService::DOMAINS,
                                ['Hak Konstitusional', 'Alasan Bernegara', 'Keuangan Negara', 'Hukum', 'Politik Bernegara'],
                            ))
                            ->required(fn (Get $get): bool => $get('score_type') === 'mastery')
                            ->visible(fn (Get $get): bool => $get('score_type') === 'mastery'),
                        Select::make('category')
                            ->label('Kategori partisipasi')
                            ->options([
                                'lapangan' => 'Lapangan',
                                'riset' => 'Riset',
                                'bantuan_hukum' => 'Bantuan Hukum',
                                'logistik' => 'Logistik',
                                'editorial' => 'Editorial',
                            ])
                            ->required(fn (Get $get): bool => $get('score_type') === 'participation')
                            ->visible(fn (Get $get): bool => $get('score_type') === 'participation'),
                        TextInput::make('delta')
                            ->label('Perubahan poin')
                            ->numeric()
                            ->rules(['required', 'integer', 'between:-100,100', 'not_in:0'])
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan audit')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (MemberReputation $record, array $data): void {
                        app(ReputationService::class)->record([
                            'user_id' => (string) $record->user_id,
                            'actor_id' => (string) auth()->id(),
                            'score_type' => $data['score_type'],
                            'domain' => $data['domain'] ?? null,
                            'category' => $data['category'] ?? null,
                            'source_type' => 'manual_adjustment',
                            'source_id' => (string) $record->user_id,
                            'delta' => (int) $data['delta'],
                            'reason' => $data['reason'],
                            'request_id' => (string) Str::uuid(),
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemberReputations::route('/'),
        ];
    }
}
