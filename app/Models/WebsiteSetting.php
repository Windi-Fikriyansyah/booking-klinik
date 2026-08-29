<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tenant_id', 'site_title', 'meta_title', 'meta_description', 'hero_title', 'hero_description', 'hero_image', 'primary_color', 'favicon', 'facebook_url', 'instagram_url', 'tiktok_url', 'google_maps_url', 'custom_domain', 'custom_domain_verified', 'is_published'])]
class WebsiteSetting extends Model
{
    use HasUuids;

    protected $casts = [
        'custom_domain_verified' => 'boolean',
        'is_published' => 'boolean',
    ];
}
