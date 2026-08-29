<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'logo', 'email', 'phone', 'whatsapp', 'address', 'city', 'province', 'postal_code', 'latitude', 'longitude', 'description', 'timezone', 'status', 'trial_ends_at'])]
class Tenant extends Model
{
    use HasUuids;

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];
}
