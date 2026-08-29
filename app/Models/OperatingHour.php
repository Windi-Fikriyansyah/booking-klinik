<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tenant_id', 'day_of_week', 'is_open', 'open_time', 'close_time'])]
class OperatingHour extends Model
{
    use HasUuids;

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
