<?php

namespace App\Filament\Resources;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\PostModerationLog;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Komunitas';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Post';

    protected static ?string $modelLabel = 'Post';

    protected static ?string $pluralModelLabel = 'Post';

    protected static ?string $slug = 'community-posts';

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
        return self::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('user.name')->label('Penulis')->disabled(),
            TextInput::make('visibility')->label('Visibilitas')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
            Textarea::make('body')->label('Teks')->rows(8)->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('body')->label('Post')->wrap()->limit(100),
                TextColumn::make('user.name')->label('Penulis')->searchable(),
                TextColumn::make('visibility')
                    ->label('Visibilitas')
                    ->formatStateUsing(fn (PostVisibility $state): string => $state->value)
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?PostStatus $state): string => $state?->value ?? '')
                    ->badge(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'published' => 'Terbit',
                    'hidden' => 'Disembunyikan',
                    'deleted' => 'Dihapus',
                ]),
                SelectFilter::make('visibility')->options([
                    'public' => 'Publik',
                    'members' => 'Anggota',
                ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Action::make('hide')
                    ->label('Sembunyikan')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => $record->status === PostStatus::Published)
                    ->action(function (Post $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->update(['status' => PostStatus::Hidden]);
                            PostModerationLog::query()->create([
                                'post_id' => $record->id,
                                'actor_id' => auth()->id(),
                                'action' => 'hidden',
                            ]);
                        });
                    }),
                Action::make('softDelete')
                    ->label('Hapus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => ! $record->trashed())
                    ->action(function (Post $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->delete();
                            PostModerationLog::query()->create([
                                'post_id' => $record->id,
                                'actor_id' => auth()->id(),
                                'action' => 'deleted',
                            ]);
                        });
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
        ];
    }
}
