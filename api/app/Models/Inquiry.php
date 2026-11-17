<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'request_id',
    'name',
    'email',
    'question',
    'status',
])]
class Inquiry extends Model
{
    use HasUuids;
}
