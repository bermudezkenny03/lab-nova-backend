<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentImage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;

class EquipmentController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Equipment::class);
            $items = Equipment::with('images','category')->orderBy('name')->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            Log::error('Error fetching equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching equipment', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreEquipmentRequest $request)
    {
        try {
            $this->authorize('create', Equipment::class);
            $validated = $request->validated();
            $equipment = Equipment::create(array_filter($validated, fn($k) => in_array($k, (new Equipment)->getFillable()), ARRAY_FILTER_USE_KEY));

            if (!empty($validated['images'])) {
                foreach ($validated['images'] as $img) {
                    $img['equipment_id'] = $equipment->id;
                    EquipmentImage::create($img);
                }
            }

            return response()->json(['message' => 'Equipment created', 'equipment' => $equipment->load('images')], 201);
        } catch (\Exception $e) {
            Log::error('Error creating equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating equipment', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $equipment = Equipment::with('images','category')->find($id);
            if (! $equipment) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $equipment);
            return response()->json(['equipment' => $equipment]);
        } catch (\Exception $e) {
            Log::error('Error fetching equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching equipment', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateEquipmentRequest $request, $id)
    {
        try {
            $equipment = Equipment::find($id);
            if (! $equipment) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('update', $equipment);
            $equipment->update($request->validated());

            return response()->json(['message' => 'Equipment updated', 'equipment' => $equipment]);
        } catch (\Exception $e) {
            Log::error('Error updating equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating equipment', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $equipment = Equipment::find($id);
            if (! $equipment) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('delete', $equipment);
            $equipment->delete();
            return response()->json(['message' => 'Equipment deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting equipment', 'error' => $e->getMessage()], 500);
        }
    }
}
