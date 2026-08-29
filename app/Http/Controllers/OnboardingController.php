<?php

namespace App\Http\Controllers;

use App\Models\OperatingHour;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string|max:100|regex:/^[a-zA-Z0-9\-]+$/',
        ]);

        $exists = WebsiteSetting::where('custom_domain', $request->subdomain)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string|max:100|regex:/^[a-zA-Z0-9\-]+$/',
            'operating_hours' => 'required|array',
            'operating_hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'operating_hours.*.is_open' => 'required|boolean',
            'operating_hours.*.open_time' => 'nullable|date_format:H:i',
            'operating_hours.*.close_time' => 'nullable|date_format:H:i',
        ]);

        $exists = WebsiteSetting::where('custom_domain', $request->subdomain)->exists();
        if ($exists) {
            return response()->json(['message' => 'Subdomain is not available.'], 422);
        }

        $tenantUser = Auth::user()->tenantUsers()->first();
        if (! $tenantUser) {
            return response()->json(['message' => 'Tenant not found.'], 403);
        }

        $tenant = $tenantUser->tenant;

        DB::transaction(function () use ($tenant, $request) {
            WebsiteSetting::create([
                'tenant_id' => $tenant->id,
                'site_title' => $tenant->name,
                'custom_domain' => $request->subdomain,
                'is_published' => true,
            ]);

            foreach ($request->operating_hours as $hour) {
                OperatingHour::create([
                    'tenant_id' => $tenant->id,
                    'day_of_week' => $hour['day_of_week'],
                    'is_open' => $hour['is_open'],
                    'open_time' => $hour['is_open'] ? $hour['open_time'] : null,
                    'close_time' => $hour['is_open'] ? $hour['close_time'] : null,
                ]);
            }
        });

        return response()->json(['message' => 'Onboarding complete!']);
    }
}
