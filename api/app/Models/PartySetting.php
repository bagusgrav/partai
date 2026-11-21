<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartySetting extends Model
{
    protected $fillable = [
        'party_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'tagline',
    ];
}
