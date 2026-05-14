<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Category;
use App\Models\EquipmentStatus;
use Illuminate\Support\Facades\DB;
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
                'equipment_active' => Equipment::where('is_active', true)->count(),
                'reservations_count' => Reservation::count(),
                'reservations_pending' => Reservation::whereHas('reservation_status', fn($q) => $q->where('slug', 'pending'))->count(),
                'reservations_approved' => Reservation::whereHas('reservation_status', fn($q) => $q->where('slug', 'approved'))->count(),
                'reports_count' => Report::count(),
                'monthly_reservations' => $this->getMonthlyReservations(),
                'equipment_by_category' => $this->getEquipmentByCategory(),
                'equipment_by_status' => $this->getEquipmentByStatus(),
                'recent_activity' => $this->getRecentActivity(),
            ];

            return response()->json(['data' => $metrics], 200);
        } catch (\Exception $e) {
            Log::error('Dashboard fetch failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch dashboard metrics'], 500);
        }
    }

    private function getMonthlyReservations(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M'),
                'count' => Reservation::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $months;
    }

    private function getEquipmentByCategory(): array
    {
        return Category::withCount('equipments')
            ->orderByDesc('equipments_count')
            ->limit(5)
            ->get()
            ->map(fn($cat) => [
                'name' => $cat->name,
                'count' => $cat->equipments_count,
            ])
            ->toArray();
    }

    private function getEquipmentByStatus(): array
    {
        return EquipmentStatus::withCount('equipments')
            ->orderByDesc('equipments_count')
            ->get()
            ->map(fn($status) => [
                'name' => $status->name,
                'count' => $status->equipments_count,
            ])
            ->toArray();
    }

    private function getRecentActivity(): array
    {
        $reservations = Reservation::with(['user', 'equipment', 'reservation_status'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'type' => 'reservation',
                'title' => $r->equipment?->name ?? 'Unknown Equipment',
                'user' => $r->user?->name ?? 'Unknown User',
                'status' => $r->status,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        $reports = Report::with(['reportRequest.user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'type' => 'report',
                'title' => $r->file_name ?? 'Report',
                'user' => $r->reportRequest?->user?->name ?? 'Unknown User',
                'status' => 'completed',
                'created_at' => $r->generated_at?->diffForHumans() ?? $r->created_at->diffForHumans(),
            ]);

        return $reservations->concat($reports)
            ->sortByDesc('created_at')
            ->take(8)
            ->values()
            ->toArray();
    }
}
