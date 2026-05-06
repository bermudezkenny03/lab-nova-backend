<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreReportEntryRequest;

class ReportController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Report::class);
            $items = Report::with('reportRequest')->orderBy('generated_at', 'desc')->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            Log::error('Error fetching reports', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching reports', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreReportEntryRequest $request)
    {
        try {
            $this->authorize('create', Report::class);
            $report = Report::create($request->validated());
            return response()->json(['message' => 'Report created', 'report' => $report], 201);
        } catch (\Exception $e) {
            Log::error('Error creating report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating report', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $report = Report::with('reportRequest')->find($id);
            if (! $report) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $report);
            return response()->json(['report' => $report]);
        } catch (\Exception $e) {
            Log::error('Error fetching report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching report', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $report = Report::find($id);
            if (! $report) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('delete', $report);
            $report->delete();
            return response()->json(['message' => 'Report deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting report', 'error' => $e->getMessage()], 500);
        }
    }
}
