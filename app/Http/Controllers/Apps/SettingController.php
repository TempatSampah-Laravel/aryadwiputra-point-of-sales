<?php
namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Show the target settings page
     */
    public function target()
    {
        $settings = [
            'monthly_sales_target' => Setting::get('monthly_sales_target', 0),
        ];

        return Inertia::render('Dashboard/Settings/Target', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update target settings
     */
    public function updateTarget(Request $request)
    {
        $request->validate([
            'monthly_sales_target' => 'required|numeric|min:0',
        ]);

        Setting::set(
            'monthly_sales_target',
            $request->monthly_sales_target,
            'Target penjualan bulanan'
        );

        return back()->with('success', 'Target berhasil disimpan');
    }
}
