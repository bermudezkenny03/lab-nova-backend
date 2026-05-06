<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Category::class);
            return response()->json(['data' => Category::orderBy('name')->get()]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching categories', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $this->authorize('create', Category::class);
            $category = Category::create($request->validated());
            return response()->json(['message' => 'Category created', 'category' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Error creating category', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating category', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::find($id);
            if (! $category) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $category);
            return response()->json(['category' => $category]);
        } catch (\Exception $e) {
            Log::error('Error fetching category', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching category', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = Category::find($id);
            if (! $category) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('update', $category);
            $category->update($request->validated());
            return response()->json(['message' => 'Category updated', 'category' => $category]);
        } catch (\Exception $e) {
            Log::error('Error updating category', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating category', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (! $category) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('delete', $category);
            $category->delete();
            return response()->json(['message' => 'Category deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting category', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting category', 'error' => $e->getMessage()], 500);
        }
    }
}
