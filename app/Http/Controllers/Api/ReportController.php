<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportRequest;
use App\Models\Report;
use App\Models\ReportFileType;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ReservationsExport;
use App\Exports\EquipmentUsageExport;
use App\Exports\UserActivityExport;

class ReportController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Report::class);
            $items = Report::with(['reportRequest.user', 'reportRequest.reportRequestType'])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            Log::error('Error fetching reports', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching reports', 'error' => $e->getMessage()], 500);
        }
    }

    public function generate(ReportRequest $reportRequest)
    {
        try {
            $this->authorize('generate', $reportRequest);

            $reportRequest->update(['report_request_status_id' => 2]);

            $type      = $reportRequest->reportRequestType->slug;
            $startDate = $reportRequest->start_date;
            $endDate   = $reportRequest->end_date;
            $format    = request()->input('format', 'csv');

            [$fileName, $filePath, $fileTypeSlug] = $this->buildFile($type, $startDate, $endDate, $format);

            $fileType = ReportFileType::where('slug', $fileTypeSlug)->firstOrFail();

            $report = Report::create([
                'report_request_id'   => $reportRequest->id,
                'file_name'           => $fileName,
                'file_path'           => Storage::url($filePath),
                'generated_at'        => now(),
                'report_file_type_id' => $fileType->id,
            ]);

            $reportRequest->update(['report_request_status_id' => 3]);

            return response()->json([
                'message' => 'Report generated successfully',
                'report'  => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating report', ['error' => $e->getMessage()]);
            $reportRequest->update(['report_request_status_id' => 4]);
            return response()->json([
                'message' => 'Error generating report',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function buildFile(string $type, string $startDate, string $endDate, string $format): array
    {
        return match ($format) {
            'xlsx' => $this->buildExcel($type, $startDate, $endDate),
            'pdf'  => $this->buildPdf($type, $startDate, $endDate),
            default => $this->buildCsv($type, $startDate, $endDate),
        };
    }

    // ─── CSV ────────────────────────────────────────────────────────────────────

    private function buildCsv(string $type, string $startDate, string $endDate): array
    {
        $fileName = "report_{$type}_{$startDate}_{$endDate}.csv";
        $filePath = "reports/{$fileName}";

        $content = match ($type) {
            'reservations'    => $this->csvReservations($startDate, $endDate),
            'equipment_usage' => $this->csvEquipmentUsage($startDate, $endDate),
            'user_activity'   => $this->csvUserActivity($startDate, $endDate),
            default           => throw new \Exception("Unknown report type: {$type}"),
        };

        Storage::disk('public')->put($filePath, $content);

        return [$fileName, $filePath, 'csv'];
    }

    private function csvReservations($startDate, $endDate): string
    {
        $rows = Reservation::with(['user', 'equipment', 'reservation_status'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time')->get();

        $csv = "ID,User,Equipment,Start Time,End Time,Status,Notes\n";
        foreach ($rows as $r) {
            $csv .= sprintf(
                "%s,\"%s %s\",\"%s\",%s,%s,\"%s\",\"%s\"\n",
                $r->id,
                $r->user->name ?? '',
                $r->user->last_name ?? '',
                $r->equipment->name ?? '',
                $r->start_time,
                $r->end_time,
                $r->reservation_status->name ?? '',
                str_replace('"', '""', $r->notes ?? '')
            );
        }
        return $csv;
    }

    private function csvEquipmentUsage($startDate, $endDate): string
    {
        $rows = Equipment::withCount([
            'reservations' => fn($q) => $q->whereBetween('start_time', [$startDate, $endDate])
        ])->get();

        $csv = "Equipment,Total Reservations\n";
        foreach ($rows as $eq) {
            $csv .= sprintf("\"%s\",%d\n", $eq->name, $eq->reservations_count);
        }
        return $csv;
    }

    private function csvUserActivity($startDate, $endDate): string
    {
        $rows = User::withCount([
            'reservations' => fn($q) => $q->whereBetween('start_time', [$startDate, $endDate])
        ])->get();

        $csv = "User,Email,Total Reservations\n";
        foreach ($rows as $u) {
            $csv .= sprintf("\"%s %s\",%s,%d\n", $u->name, $u->last_name, $u->email, $u->reservations_count);
        }
        return $csv;
    }

    // ─── EXCEL ──────────────────────────────────────────────────────────────────

    private function buildExcel(string $type, string $startDate, string $endDate): array
    {
        $fileName = "report_{$type}_{$startDate}_{$endDate}.xlsx";
        $filePath = "reports/{$fileName}";

        $export = match ($type) {
            'reservations'    => new ReservationsExport($startDate, $endDate),
            'equipment_usage' => new EquipmentUsageExport($startDate, $endDate),
            'user_activity'   => new UserActivityExport($startDate, $endDate),
            default           => throw new \Exception("Unknown report type: {$type}"),
        };

        Excel::store($export, $filePath, 'public');

        return [$fileName, $filePath, 'xlsx'];
    }

    // ─── PDF ────────────────────────────────────────────────────────────────────

    private function buildPdf(string $type, string $startDate, string $endDate): array
    {
        $fileName = "report_{$type}_{$startDate}_{$endDate}.pdf";
        $filePath = "reports/{$fileName}";

        [$view, $data] = match ($type) {
            'reservations' => [
                'reports.reservations',
                ['rows' => Reservation::with(['user', 'equipment', 'reservation_status'])->whereBetween('start_time', [$startDate, $endDate])->orderBy('start_time')->get(), 'startDate' => $startDate, 'endDate' => $endDate],
            ],
            'equipment_usage' => [
                'reports.equipment_usage',
                ['rows' => Equipment::withCount(['reservations' => fn($q) => $q->whereBetween('start_time', [$startDate, $endDate])])->get(), 'startDate' => $startDate, 'endDate' => $endDate],
            ],
            'user_activity' => [
                'reports.user_activity',
                ['rows' => User::withCount(['reservations' => fn($q) => $q->whereBetween('start_time', [$startDate, $endDate])])->get(), 'startDate' => $startDate, 'endDate' => $endDate],
            ],
            default => throw new \Exception("Unknown report type: {$type}"),
        };

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
        Storage::disk('public')->put($filePath, $pdf->output());

        return [$fileName, $filePath, 'pdf'];
    }

    public function show($id)
    {
        try {
            $report = Report::with(['reportRequest.user'])->find($id);
            if (!$report) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('view', $report);
            return response()->json(['report' => $report]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching report', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $report = Report::find($id);
            if (!$report) return response()->json(['message' => 'Not found'], 404);
            $this->authorize('delete', $report);

            if ($report->file_path) {
                $path = str_replace('/storage/', '', $report->file_path);
                Storage::disk('public')->delete($path);
            }

            $report->delete();
            return response()->json(['message' => 'Report deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting report', 'error' => $e->getMessage()], 500);
        }
    }
}
