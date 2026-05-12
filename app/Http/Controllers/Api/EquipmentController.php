<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Models\EquipmentImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Equipment::class);

            $items = Equipment::with(['images', 'category', 'status'])
                ->ordered()
                ->get();

            return response()->json([
                'data' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching equipment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching equipment',
            ], 500);
        }
    }

    public function store(StoreEquipmentRequest $request)
    {
        try {
            $this->authorize('create', Equipment::class);

            $equipment = DB::transaction(function () use ($request) {
                $validated = $request->validated();

                $equipmentData = collect($validated)
                    ->except(['equipment_images', 'code'])
                    ->only((new Equipment())->getFillable())
                    ->toArray();

                $equipment = Equipment::create($equipmentData);

                EquipmentImage::saveFiles(
                    $request->file('equipment_images'),
                    $equipment->id
                );

                return $equipment;
            });

            return response()->json([
                'message' => 'Equipment created',
                'equipment' => $equipment->fresh(['images', 'category', 'status']),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error creating equipment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error creating equipment',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $equipment = Equipment::with(['images', 'category', 'status'])->find($id);

            if (! $equipment) {
                return response()->json([
                    'message' => 'Not found',
                ], 404);
            }

            $this->authorize('view', $equipment);

            return response()->json([
                'equipment' => $equipment,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching equipment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching equipment',
            ], 500);
        }
    }

    public function update(UpdateEquipmentRequest $request, $id)
    {
        try {
            $equipment = Equipment::find($id);

            if (! $equipment) {
                return response()->json([
                    'message' => 'Not found',
                ], 404);
            }

            $this->authorize('update', $equipment);

            DB::transaction(function () use ($request, $equipment) {
                $validated = $request->validated();

                $equipmentData = collect($validated)
                    ->except(['equipment_images', 'code'])
                    ->only($equipment->getFillable())
                    ->toArray();

                $equipment->update($equipmentData);

                if ($request->hasFile('equipment_images')) {
                    EquipmentImage::replaceFiles(
                        $request->file('equipment_images'),
                        $equipment->id
                    );
                }
            });

            return response()->json([
                'message' => 'Equipment updated',
                'equipment' => $equipment->fresh(['images', 'category', 'status']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error updating equipment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error updating equipment',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $equipment = Equipment::find($id);

            if (! $equipment) {
                return response()->json([
                    'message' => 'Not found',
                ], 404);
            }

            $this->authorize('delete', $equipment);

            DB::transaction(function () use ($equipment) {
                $equipment->delete();
            });

            return response()->json([
                'message' => 'Equipment deleted',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error deleting equipment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error deleting equipment',
            ], 500);
        }
    }
}
