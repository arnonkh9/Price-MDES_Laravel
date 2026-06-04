<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Support\Reports;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    /** Resolve and validate the requested report type + filters from the query string. */
    private function resolve(Request $request): array
    {
        $type = (string) $request->input('type', 'price');
        if (! Reports::isValidType($type)) {
            $type = 'price';
        }

        $filters = [
            'year'     => (string) $request->input('year', 'all'),
            'category' => (string) $request->input('category', 'all'),
        ];

        return [$type, $filters];
    }

    public function pdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('reports', 'view'), 403);
        [$type, $filters] = $this->resolve($request);

        $report = Reports::build($type, $filters);

        $pdf = Pdf::loadView('exports.report-pdf', compact('report', 'filters'))
            ->setPaper('a4', Reports::orientation($type));

        $filename = 'รายงาน_' . preg_replace('/[^\w\x{0E00}-\x{0E7F}\-]/u', '_', Reports::label($type)) . '.pdf';

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('reports', 'view'), 403);
        [$type, $filters] = $this->resolve($request);

        $filename = 'รายงาน_' . preg_replace('/[^\w\x{0E00}-\x{0E7F}\-]/u', '_', Reports::label($type)) . '.xlsx';

        return Excel::download(new ReportExport($type, $filters), $filename);
    }
}
