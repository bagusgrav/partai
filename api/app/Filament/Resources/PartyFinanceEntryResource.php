<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartyFinanceEntryResource\Pages;
use App\Models\PartyFinanceEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartyFinanceEntryResource extends Resource
{
    protected static ?string $model = PartyFinanceEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Keuangan Partai';

    protected static ?string $modelLabel = 'Entri Keuangan';

    protected static ?string $pluralModelLabel = 'Buku Besar Keuangan';

    protected static ?string $slug = 'keuangan-partai';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus', 'panelis']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'pengurus']) ?? false;
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
            Select::make('entry_type')
                ->options(['inflow' => 'Pemasukan', 'outflow' => 'Pengeluaran', 'reversal' => 'Pembalik'])
                ->required(),
            Select::make('channel')
                ->options([
                    'member_dues' => 'Iuran Anggota',
                    'individual_donation' => 'Sumbangan Individu',
                    'corporate_donation' => 'Sumbangan Badan Usaha',
                    'political_education_expense' => 'Pendidikan Politik',
                    'legal_aid_expense' => 'Bantuan Hukum',
                    'operational_expense' => 'Operasional',
                    'audit_reversal' => 'Koreksi Pembalik',
                ])
                ->required(),
            TextInput::make('amount_idr')->numeric()->minValue(1)->required(),
            DatePicker::make('transaction_date')->required(),
            Select::make('donor_type')
                ->options([
                    'member' => 'Anggota',
                    'individual' => 'Individu',
                    'corporate' => 'Badan Usaha',
                    'internal_treasury' => 'Kas Internal',
                ])
                ->required(),
            TextInput::make('contributor_display_name')->required()->maxLength(255),
            TextInput::make('purpose_or_program')->required()->maxLength(220),
            TextInput::make('evidence_reference')->maxLength(255),
            TextInput::make('province_name')->maxLength(255),
            Toggle::make('legal_declaration_accepted')->default(false),
            Select::make('reversal_of_id')
                ->label('Entri yang Dibalik')
                ->relationship('reversalOf', 'purpose_or_program')
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('sequence'))
            ->columns([
                TextColumn::make('sequence')->label('#')->sortable(),
                TextColumn::make('transaction_date')->label('Tanggal')->date(),
                TextColumn::make('entry_type')->label('Jenis')->badge(),
                TextColumn::make('channel')->label('Kategori')->searchable(),
                TextColumn::make('contributor_display_name')->label('Penyumbang')->wrap(),
                TextColumn::make('amount_idr')->label('Nominal')->money('idr', 0),
                TextColumn::make('compliance_status')->label('Status')->badge(),
                TextColumn::make('hash')->label('SHA-256')->limit(18)->copyable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('compliance_status')
                    ->options([
                        'pending_audit' => 'Menunggu Audit',
                        'verified' => 'Terverifikasi',
                        'rejected_refunded' => 'Ditolak/Dikembalikan',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('audit')
                    ->label('Verifikasi Audit')
                    ->visible(fn (): bool => auth()->user()?->hasRole('panelis') ?? false)
                    ->requiresConfirmation()
                    ->form([
                        Select::make('compliance_status')
                            ->options(['verified' => 'Verified', 'rejected_refunded' => 'Rejected / Refunded'])
                            ->required(),
                        Textarea::make('audit_note')->required()->maxLength(5000),
                    ])
                    ->action(function (PartyFinanceEntry $record, array $data): void {
                        $record->update([
                            'compliance_status' => $data['compliance_status'],
                            'audited_by' => auth()->id(),
                            'audited_at' => now(),
                            'audit_note' => $data['audit_note'],
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartyFinanceEntries::route('/'),
            'create' => Pages\CreatePartyFinanceEntry::route('/create'),
        ];
    }
}
