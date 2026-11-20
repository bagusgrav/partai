<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Faq;
use App\Models\Inquiry;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Komunikasi Publik';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Pertanyaan Masuk';

    protected static ?string $modelLabel = 'Pertanyaan Masuk';

    protected static ?string $pluralModelLabel = 'Pertanyaan Masuk';

    protected static ?string $slug = 'pertanyaan-masuk';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
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
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('question')->label('Pertanyaan')->searchable()->wrap(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'answered' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')->label('Diterima')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('makeFaq')
                    ->label('Jadikan FAQ')
                    ->icon('heroicon-o-question-mark-circle')
                    ->visible(fn (Inquiry $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('answer')
                            ->label('Jawaban FAQ')
                            ->required()
                            ->rows(5)
                            ->maxLength(10000),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $inquiry = Inquiry::query()
                                ->lockForUpdate()
                                ->findOrFail($record->getKey());

                            if ($inquiry->status !== 'pending') {
                                throw ValidationException::withMessages([
                                    'record' => 'Pertanyaan ini sudah diproses.',
                                ]);
                            }

                            Faq::query()->create([
                                'question' => $inquiry->question,
                                'answer' => $data['answer'],
                                'is_published' => true,
                            ]);

                            $inquiry->update(['status' => 'answered']);
                        });
                    })
                    ->successNotificationTitle('Pertanyaan berhasil dijadikan FAQ'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
        ];
    }
}
