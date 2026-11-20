<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostReportResource\Pages;
use App\Models\PostModerationLog;
use App\Models\PostReport;
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
use Illuminate\Support\Facades\DB;

class PostReportResource extends Resource
{
    protected static ?string $model = PostReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Komunitas';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Laporan Post';

    protected static ?string $modelLabel = 'Laporan Post';

    protected static ?string $pluralModelLabel = 'Laporan Post';

    protected static ?string $slug = 'community-post-reports';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
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
            TextInput::make('post_id')->label('ID Post')->disabled(),
            TextInput::make('reporter.name')->label('Pelapor')->disabled(),
            Textarea::make('reason')->label('Alasan')->rows(5)->disabled()->columnSpanFull(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')->label('Post')->searchable(),
                TextColumn::make('reporter.name')->label('Pelapor'),
                TextColumn::make('reason')->label('Alasan')->wrap()->limit(100),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Dilaporkan')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Terbuka',
                    'reviewed' => 'Ditinjau',
                    'dismissed' => 'Ditolak',
                ]),
            ])
            ->actions([
                ViewAction::make()->label('Lihat'),
                Action::make('review')
                    ->label('Tandai ditinjau')
                    ->visible(fn (PostReport $record): bool => $record->status === 'open')
                    ->action(fn (PostReport $record) => self::updateReportStatus($record, 'reviewed')),
                Action::make('dismiss')
                    ->label('Tutup laporan')
                    ->color('gray')
                    ->visible(fn (PostReport $record): bool => $record->status === 'open')
                    ->action(fn (PostReport $record) => self::updateReportStatus($record, 'dismissed')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostReports::route('/'),
        ];
    }

    private static function updateReportStatus(PostReport $report, string $status): void
    {
        DB::transaction(function () use ($report, $status): void {
            $report->update(['status' => $status]);
            PostModerationLog::query()->create([
                'post_id' => $report->post_id,
                'actor_id' => auth()->id(),
                'action' => 'report_'.$status,
            ]);
        });
    }
}
