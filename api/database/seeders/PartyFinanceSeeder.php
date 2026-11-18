<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PartyFinanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartyFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PartyFinanceService::class);
        $admin = User::query()->where('email', 'admin@partai.id')->first();
        $year = (int) date('Y');

        $entries = [
            ['member_dues', 'Anggota Wilayah Jawa Barat', 'member', 5000000, '36', 'Jawa Barat', 'Verified'],
            ['member_dues', 'Anggota Wilayah Jawa Timur', 'member', 7500000, '35', 'Jawa Timur', 'Verified'],
            ['member_dues', 'Anggota Wilayah DKI Jakarta', 'member', 4000000, '31', 'DKI Jakarta', 'Verified'],
            ['member_dues', 'Anggota Wilayah Sulawesi Selatan', 'member', 3000000, '73', 'Sulawesi Selatan', 'Verified'],
            ['individual_donation', 'DR. Siti Aminah', 'individual', 250000000, null, null, 'Verified'],
            ['individual_donation', 'Budi Santoso', 'individual', 75000000, null, null, 'Verified'],
            ['corporate_donation', 'PT Amanah Nusantara', 'corporate', 500000000, null, null, 'Verified'],
            ['corporate_donation', 'CV Warga Karya', 'corporate', 250000000, null, null, 'Verified'],
            ['political_education_expense', 'Kas Internal', 'internal_treasury', 350000000, null, null, 'Verified'],
            ['political_education_expense', 'Kas Internal', 'internal_treasury', 180000000, null, null, 'Verified'],
            ['legal_aid_expense', 'Kas Internal', 'internal_treasury', 120000000, null, null, 'Verified'],
            ['operational_expense', 'Kas Internal', 'internal_treasury', 90000000, null, null, 'Verified'],
            ['member_dues', 'Anggota Wilayah Bali', 'member', 2000000, '51', 'Bali', 'Pending'],
            ['operational_expense', 'Kas Internal', 'internal_treasury', 25000000, null, null, 'Verified'],
        ];

        foreach ($entries as $index => $entry) {
            [$channel, $name, $donorType, $amount, $regionCode, $province, $status] = $entry;
            $compliance = $status === 'Verified' ? 'verified' : 'pending_audit';
            $isOutflow = str_ends_with($channel, '_expense');
            $identityHash = in_array($donorType, ['individual', 'corporate'], true)
                ? hash('sha256', $donorType === 'individual'
                    ? str_pad((string) (3200000000000000 + $index), 16, '0', STR_PAD_LEFT)
                    : str_pad((string) (100000000000000 + $index), 15, '0', STR_PAD_LEFT))
                : null;

            $service->recordTransaction([
                'request_id' => (string) Str::uuid(),
                'entry_type' => $isOutflow ? 'outflow' : 'inflow',
                'channel' => $channel,
                'fiscal_year' => $year,
                'transaction_date' => sprintf('%04d-%02d-%02d', $year, 1 + ($index % 9), 1 + ($index % 28)),
                'amount_idr' => $amount,
                'donor_type' => $donorType,
                'user_id' => null,
                'contributor_display_name' => $name,
                'identity_hash' => $identityHash,
                'masked_identity' => $identityHash ? (
                    $donorType === 'individual'
                        ? 'NIK ••••••••'.substr(hash('sha256', (string) $index), -4)
                        : 'NPWP 01.•••.•••.0-000.000'
                ) : null,
                'legal_declaration_accepted' => $donorType !== 'internal_treasury',
                'purpose_or_program' => $isOutflow
                    ? match ($channel) {
                        'political_education_expense' => 'Pendidikan politik dan kaderisasi warga',
                        'legal_aid_expense' => 'Bantuan hukum aksi lapangan',
                        default => 'Operasional dan administrasi transparan',
                    }
                    : 'Penerimaan kas partai',
                'region_code' => $regionCode,
                'province_name' => $province,
                'evidence_reference' => 'SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'compliance_status' => $compliance,
                'recorded_by' => $admin?->id,
                'reversal_of_id' => null,
            ]);
        }
    }
}
