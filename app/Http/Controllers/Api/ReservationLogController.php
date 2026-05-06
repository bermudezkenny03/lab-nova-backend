<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReservationLog;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreReservationLogRequest;

class ReservationLogController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Reservation::class);
            $logs = ReservationLog::with(['reservation','user'])->orderBy('created_at','desc')->get();
            return response()->json(['data' => $logs]);
        } catch (\Exception $e) {
            Log::error('Error fetching reservation logs', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching logs', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreReservationLogRequest $request)
    {
        try {
            $reservation = Reservation::find($request->validated()['reservation_id']);
            if (! $reservation) return response()->json(['message' => 'Reservation not found'], 404);
            $this->authorize('view', $reservation);

            $log = ReservationLog::create($request->validated());
            return response()->json(['message' => 'Log created', 'log' => $log], 201);
        } catch (\Exception $e) {
            Log::error('Error creating reservation log', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating log', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $log = ReservationLog::with(['reservation','user'])->find($id);
            if (! $log) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $log->reservation);
            return response()->json(['log' => $log]);
        } catch (\Exception $e) {
            Log::error('Error fetching reservation log', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching log', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $log = ReservationLog::find($id);
            if (! $log) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $log->reservation);
            $log->delete();
            return response()->json(['message' => 'Log deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting reservation log', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting log', 'error' => $e->getMessage()], 500);
        }
    }
}
