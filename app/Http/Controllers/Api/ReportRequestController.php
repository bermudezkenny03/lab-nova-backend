<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreReportRequestRequest;
use App\Http\Requests\UpdateReportRequestRequest;

class ReportRequestController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', ReportRequest::class);
            $items = ReportRequest::with('user')->orderBy('created_at','desc')->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            Log::error('Error fetching report requests', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching report requests', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreReportRequestRequest $request)
    {
        try {
            $this->authorize('create', ReportRequest::class);
            $req = ReportRequest::create($request->validated());
            return response()->json(['message' => 'Report request created', 'report_request' => $req], 201);
        } catch (\Exception $e) {
            Log::error('Error creating report request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating report request', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $req = ReportRequest::with('reports')->find($id);
            if (! $req) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $req);
            return response()->json(['report_request' => $req]);
        } catch (\Exception $e) {
            Log::error('Error fetching report request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching report request', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateReportRequestRequest $request, $id)
    {
        try {
            $req = ReportRequest::find($id);
            if (! $req) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('update', $req);

            $req->update($request->validated());

            return response()->json(['message' => 'Report request updated', 'report_request' => $req]);
        } catch (\Exception $e) {
            Log::error('Error updating report request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating report request', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $req = ReportRequest::find($id);
            if (! $req) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('delete', $req);
            $req->delete();
            return response()->json(['message' => 'Report request deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting report request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting report request', 'error' => $e->getMessage()], 500);
        }
    }
}
