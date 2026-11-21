<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class MemberNumberGenerator
{
    public function generate(): string
    {
        $prefix = 'PRT-'.now()->format('Y').'-';
        $lastNumber = User::query()
            ->where('member_number', 'like', $prefix.'%')
            ->orderByDesc('member_number')
            ->value('member_number');
        $nextSequence = $lastNumber === null ? 1 : (int) substr($lastNumber, -4) + 1;

        if ($nextSequence > 9999) {
            throw new RuntimeException('Nomor anggota untuk tahun ini sudah mencapai batas.');
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
