<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CivicQuestionResource\Pages;
use App\Models\CivicQuestion;
use App\Services\ReputationService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CivicQuestionResource extends Resource
{
    protected static ?string $model = CivicQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Pendidikan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Bank Soal';

    protected static ?string $modelLabel = 'Soal Kewargaan';

    protected static ?string $pluralModelLabel = 'Soal Kewargaan';

    protected static ?string $slug = 'civic-questions';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus', 'panelis']) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return self::canViewAny();
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return self::canViewAny();
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
        return $form->schema([
            Select::make('domain')
                ->label('Domain')
                ->options(collect(ReputationService::DOMAINS)->mapWithKeys(fn (string $domain): array => [
                    $domain => str($domain)->replace('_', ' ')->title()->toString(),
                ]))
                ->required(),
            Select::make('mastery_level')
                ->label('Tingkat Penguasaan')
                ->options(CivicQuestion::MASTERY_LEVELS)
                ->required(),
            Textarea::make('prompt')
                ->label('Skenario / Pertanyaan')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
            Repeater::make('options')
                ->label('Pilihan Jawaban')
                ->schema([
                    Select::make('key')
                        ->label('Kunci Opsi')
                        ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'])
                        ->required(),
                    TextInput::make('text')
                        ->label('Teks Opsi')
                        ->required()
                        ->maxLength(1000),
                ])
                ->minItems(4)
                ->maxItems(4)
                ->defaultItems(4)
                ->columns(2)
                ->required()
                ->columnSpanFull(),
            Select::make('correct_option')
                ->label('Jawaban Benar')
                ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'])
                ->required(),
            Textarea::make('explanation')
                ->label('Penjelasan Rasional')
                ->required()
                ->rows(5)
                ->columnSpanFull(),
            TextInput::make('source_citation')
                ->label('Sitasi Hukum / Data Primer')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('source_url')
                ->label('URL Rujukan Resmi')
                ->url()
                ->nullable()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('review_status')
                ->label('Status Editorial')
                ->options([
                    'draft' => 'Draft',
                    'under_review' => 'Dalam Peninjauan',
                    'published' => 'Terpublikasi',
                ])
                ->default('draft')
                ->required(),
            TextInput::make('mastery_points')
                ->label('Poin Penguasaan')
                ->numeric()
                ->minValue(1)
                ->maxValue(255)
                ->default(5)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prompt')->label('Pertanyaan')->searchable()->wrap()->limit(90),
                TextColumn::make('domain')->label('Domain')->badge(),
                TextColumn::make('mastery_level')->label('Level')->sortable(),
                TextColumn::make('review_status')->label('Status')->badge(),
                TextColumn::make('source_citation')->label('Sumber')->wrap()->limit(70),
            ])
            ->filters([
                SelectFilter::make('domain')->options(collect(ReputationService::DOMAINS)->mapWithKeys(fn (string $domain): array => [
                    $domain => str($domain)->replace('_', ' ')->title()->toString(),
                ])),
                SelectFilter::make('mastery_level')->options(CivicQuestion::MASTERY_LEVELS),
                SelectFilter::make('review_status')->options([
                    'draft' => 'Draft',
                    'under_review' => 'Dalam Peninjauan',
                    'published' => 'Terpublikasi',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tinjau / Ubah'),
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
            'index' => Pages\ListCivicQuestions::route('/'),
            'create' => Pages\CreateCivicQuestion::route('/create'),
            'edit' => Pages\EditCivicQuestion::route('/{record}/edit'),
        ];
    }
}
