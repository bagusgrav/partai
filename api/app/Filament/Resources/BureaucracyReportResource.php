<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BureaucracyReportResource\Pages;
use App\Models\BureaucracyReport;
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

class BureaucracyReportResource extends Resource
{
    protected static ?string $model = BureaucracyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Pengawasan Birokrasi';

    protected static ?string $navigationLabel = 'Laporan Warga';

    protected static ?string $modelLabel = 'Laporan Warga';

    protected static ?string $pluralModelLabel = 'Laporan Warga';

    protected static ?string $slug = 'laporan-birokrasi';

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
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus', 'panelis']) ?? false;
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
            TextInput::make('ticket_code')->disabled(),
            TextInput::make('agency_name')->disabled(),
            TextInput::make('title')->disabled(),
            Textarea::make('chronology')->disabled()->rows(5)->columnSpanFull(),
            Select::make('verification_status')->options([
                'under_review' => 'Dalam Peninjauan',
                'verified' => 'Terverifikasi',
                'forwarded_to_ombudsman' => 'Diteruskan ke Ombudsman',
                'resolved' => 'Selesai',
                'rejected' => 'Ditolak',
            ])->required(),
            Textarea::make('public_resolution_note')->label('Catatan Tindak Lanjut Publik')->rows(4)->nullable()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_code')->label('Tiket')->searchable()->sortable(),
                TextColumn::make('agency_name')->label('Instansi')->searchable()->wrap(),
                TextColumn::make('category')->label('Kategori')->badge(),
                TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(70),
                TextColumn::make('verification_status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'resolved' => 'success',
                    'rejected' => 'danger',
                    'forwarded_to_ombudsman' => 'warning',
                    'verified' => 'info',
                    default => 'gray',
                })->sortable(),
                TextColumn::make('verified_at')->label('Diverifikasi')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('verification_status')->options([
                    'submitted' => 'Diajukan',
                    'under_review' => 'Dalam Peninjauan',
                    'verified' => 'Terverifikasi',
                    'forwarded_to_ombudsman' => 'Diteruskan',
                    'resolved' => 'Selesai',
                    'rejected' => 'Ditolak',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tinjau'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBureaucracyReports::route('/'),
            'edit' => Pages\EditBureaucracyReport::route('/{record}/edit'),
        ];
    }
}
