<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\Report;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('view', Dashboard::class);

            $metrics = [
                'users_count' => User::count(),
                'equipment_count' => Equipment::count(),
                'reservations_count' => Reservation::count(),
                'reservations_pending' => Reservation::where('status', 'pending')->count(),
                'reports_count' => Report::count(),
                'recent_reservations' => Reservation::latest()->limit(5)->get(),
                'recent_reports' => Report::latest()->limit(5)->get(),
            ];

            return response()->json(['data' => $metrics], 200);
        } catch (\Exception $e) {
            Log::error('Dashboard fetch failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch dashboard metrics'], 500);
        }
    }
}
