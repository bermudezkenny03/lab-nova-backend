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
        $items = Reservation::with(['user','equipment','reservation_status'])->orderBy('start_time','desc')->get();
        return response()->json(['data' => $items]);
    }

    public function getGeneralData()
    {
        $this->authorize('viewAny', Reservation::class);

        $user = auth()->user();
        $usersQuery = \App\Models\User::select('id', 'name', 'last_name', 'role_id');

        // Students only see themselves
        if ($user->isStudent()) {
            $usersQuery->where('id', $user->id);
        }

        return response()->json([
            'data' => [
                'users' => $usersQuery->get(),
                'equipments' => \App\Models\Equipment::select('id', 'name')->get(),
                'statuses' => \App\Models\ReservationStatus::select('id', 'name', 'slug')->get(),
            ]
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $this->authorize('create', Reservation::class);
            $validated = $request->validated();

            // Students can only create reservations for themselves
            $user = auth()->user();
            if ($user->isStudent()) {
                $validated['user_id'] = $user->id;
            }

            if (!isset($validated['status'])) {
                $validated['status'] = 'pending';
            }

            // Convert status slug to reservation_status_id
            $statusModel = \App\Models\ReservationStatus::where('slug', $validated['status'])->first();
            if ($statusModel) {
                $validated['reservation_status_id'] = $statusModel->id;
            }
            unset($validated['status']);

            // Prevent overlapping reservations for the same equipment
            $conflict = Reservation::where('equipment_id', $validated['equipment_id'])
                ->whereHas('reservation_status', fn($q) => $q->whereIn('slug', ['pending', 'approved']))
                ->where(function ($q) use ($validated) {
                    $q->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
                })->exists();

            if ($conflict) {
                return response()->json(['message' => 'Conflict: equipment already reserved for the provided time range.'], 422);
            }

            $reservation = Reservation::create($validated);

            // Log creation
            $actionId = \App\Models\ReservationLogAction::where('slug', 'created')->value('id');
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'user_id' => $validated['user_id'] ?? null,
                'reservation_log_action_id' => $actionId,
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
        $reservation = Reservation::with(['user','equipment','logs','reservation_status'])->find($id);
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
                ->whereHas('reservation_status', fn($q) => $q->whereIn('slug', ['pending', 'approved']))
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
                $actionSlug = in_array($validated['status'], ['approved', 'rejected', 'cancelled', 'completed'])
                    ? $validated['status']
                    : 'updated';
                $actionId = \App\Models\ReservationLogAction::where('slug', $actionSlug)->value('id');
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $validated['approved_by'] ?? null,
                    'reservation_log_action_id' => $actionId,
                    'description' => "Status changed from {$oldStatus} to {$validated['status']}",
                ]);
            } else {
                $actionId = \App\Models\ReservationLogAction::where('slug', 'updated')->value('id');
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $validated['approved_by'] ?? null,
                    'reservation_log_action_id' => $actionId,
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

    public function approve($id)
    {
        try {
            $reservation = Reservation::find($id);
            if (! $reservation) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('approve', $reservation);

            $reservation->status = 'approved';
            $reservation->approved_by = auth()->id();
            $reservation->approved_at = now();
            $reservation->save();

            $actionId = \App\Models\ReservationLogAction::where('slug', 'approved')->value('id');
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
                'reservation_log_action_id' => $actionId,
                'description' => 'Reservation approved',
            ]);

            return response()->json(['message' => 'Reservation approved', 'reservation' => $reservation]);
        } catch (\Exception $e) {
            Log::error('Error approving reservation', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error approving reservation', 'error' => $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $reservation = Reservation::find($id);
            if (! $reservation) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('reject', $reservation);

            $reservation->status = 'rejected';
            $reservation->approved_by = auth()->id();
            $reservation->approved_at = now();
            $reservation->save();

            $actionId = \App\Models\ReservationLogAction::where('slug', 'rejected')->value('id');
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
                'reservation_log_action_id' => $actionId,
                'description' => 'Reservation rejected',
            ]);

            return response()->json(['message' => 'Reservation rejected', 'reservation' => $reservation]);
        } catch (\Exception $e) {
            Log::error('Error rejecting reservation', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error rejecting reservation', 'error' => $e->getMessage()], 500);
        }
    }
}
