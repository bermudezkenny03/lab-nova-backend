<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationLog;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;

class ReservationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Reservation::class);
        $items = Reservation::with(['user','equipment'])->orderBy('start_time','desc')->get();
        return response()->json(['data' => $items]);
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $this->authorize('create', Reservation::class);
            $validated = $request->validated();

            // Prevent overlapping reservations for the same equipment
            $conflict = Reservation::where('equipment_id', $validated['equipment_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($validated) {
                    $q->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
                })->exists();

            if ($conflict) {
                return response()->json(['message' => 'Conflict: equipment already reserved for the provided time range.'], 422);
            }

            $reservation = Reservation::create($validated);

            // Log creation
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'user_id' => $validated['user_id'] ?? null,
                'action' => 'created',
                'description' => 'Reservation created',
            ]);

            return response()->json(['message' => 'Reservation created', 'reservation' => $reservation], 201);
        } catch (\Exception $e) {
            Log::error('Error creating reservation', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating reservation', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $reservation = Reservation::with(['user','equipment','logs'])->find($id);
        if (! $reservation) return response()->json(['message' => 'Not found'], 404);
        $this->authorize('view', $reservation);
        return response()->json(['reservation' => $reservation]);
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        try {
            $reservation = Reservation::find($id);
            if (! $reservation) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('update', $reservation);

            $validated = $request->validated();

            // If times or equipment change, check for conflicts
            $newEquipmentId = $validated['equipment_id'] ?? $reservation->equipment_id;
            $newStart = $validated['start_time'] ?? $reservation->start_time;
            $newEnd = $validated['end_time'] ?? $reservation->end_time;

            $conflict = Reservation::where('equipment_id', $newEquipmentId)
                ->whereIn('status', ['pending', 'approved'])
                ->where('id', '!=', $reservation->id)
                ->where(function ($q) use ($newStart, $newEnd) {
                    $q->where('start_time', '<', $newEnd)
                      ->where('end_time', '>', $newStart);
                })->exists();

            if ($conflict) {
                return response()->json(['message' => 'Conflict: equipment already reserved for the provided time range.'], 422);
            }

            $oldStatus = $reservation->status;

            $reservation->update($validated);

            // Log status changes or updates
            if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $validated['approved_by'] ?? null,
                    'action' => 'status_updated',
                    'description' => "Status changed from {$oldStatus} to {$validated['status']}",
                ]);
            } else {
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $validated['approved_by'] ?? null,
                    'action' => 'updated',
                    'description' => 'Reservation updated',
                ]);
            }

            return response()->json(['message' => 'Reservation updated', 'reservation' => $reservation]);
        } catch (\Exception $e) {
            Log::error('Error updating reservation', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating reservation', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $reservation = Reservation::find($id);
        if (! $reservation) return response()->json(['message' => 'Not found'], 404);
        $this->authorize('delete', $reservation);
        $reservation->delete();
        return response()->json(['message' => 'Reservation deleted']);
    }
}
