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
            $items = Equipment::with('images', 'category')->orderBy('name')->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            Log::error('Error fetching equipment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching equipment', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreEquipmentRequest $request)
    {
        $this->authorize('create', Equipment::class);

        $validated = $request->validated();

        $equipment = Equipment::create(
            array_intersect_key(
                $validated,
                array_flip((new Equipment)->getFillable())
            )
        );

        if ($request->hasFile('equipment_images')) {
            EquipmentImage::saveFiles($request->file('equipment_images'), $equipment->id);
        }

        return response()->json([
            'message' => 'Equipment created',
            'equipment' => $equipment->load('images')
        ], 201);
    }

    public function show($id)
    {
        try {
            $equipment = Equipment::with('images', 'category')->find($id);
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
