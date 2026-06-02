<?php

namespace App\Http\Controllers;

use App\Exports\BulkCharacteristicsExport;
use App\Exports\BulkComparisonsExport;
use App\Exports\CharacteristicsExport;
use App\Exports\ComparisonExport;
use App\Exports\SampleCharacteristicsExport;
use App\Exports\SampleProductsExport;
use App\Models\CharacteristicsTemplate;
use App\Models\Comparison;
use App\Models\Product;
use App\Support\Specs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function products(): StreamedResponse
    {
        abort_unless(auth()->user()->hasPermission('products', 'export'), 403);

        // Build headers: core fields + all spec columns
        $headers = ['id', 'category', 'brand', 'model', 'price', 'priceDate', 'priceUnit', 'priceSource', 'priceUrl'];

        // Add spec field headers from config
        $specFields = [];
        foreach (Specs::groups() as $group) {
            foreach ($group['fields'] as $field) {
                $headers[] = $field;
                $specFields[] = $field;
            }
        }

        // Mapping of header labels to model attributes
        $map = [
            'id' => 'id',
            'category' => 'category',
            'brand' => 'brand',
            'model' => 'model',
            'price' => 'price',
            'priceDate' => 'price_date',
            'priceUnit' => 'price_unit',
            'priceSource' => 'price_source',
            'priceUrl' => 'price_url',
        ];

        $callback = function () use ($headers, $map, $specFields) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, $headers);

            foreach (Product::all() as $p) {
                $row = [];

                // Core fields
                foreach ($map as $header => $field) {
                    $row[$header] = $p->{$field};
                }

                // Spec fields (from JSONB specs column)
                $specs = $p->specs ?? [];
                foreach ($specFields as $field) {
                    $row[$field] = $specs[$field] ?? '';
                }

                // Output row in header order
                $output = [];
                foreach ($headers as $h) {
                    $output[] = $row[$h] ?? '';
                }
                fputcsv($out, $output);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'ราคากลาง.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function comparison(Comparison $comparison)
    {
        abort_unless(auth()->user()->hasPermission('comparisons', 'export'), 403);

        $comparison->load('vendors');

        return Excel::download(
            new ComparisonExport($comparison),
            'เปรียบเทียบ_'.$comparison->name.'.xlsx'
        );
    }

    public function comparisonsMultipleSheets(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('comparisons', 'export'), 403);

        $ids = explode(',', $request->input('ids', ''));
        $ids = array_filter($ids);
        if (empty($ids)) {
            abort(400, 'No comparisons selected');
        }

        $comparisons = Comparison::with('vendors')
            ->whereIn('id', $ids)
            ->get();

        if ($comparisons->isEmpty()) {
            abort(404, 'Comparisons not found');
        }

        return Excel::download(
            new BulkComparisonsExport($comparisons),
            'เปรียบเทียบ_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function characteristics(CharacteristicsTemplate $spec)
    {
        abort_unless(auth()->user()->hasPermission('specs', 'export'), 403);

        return Excel::download(
            new CharacteristicsExport($spec),
            'คุณลักษณะ_' . $spec->name . '.xlsx'
        );
    }

    public function bulkCharacteristics(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('specs', 'export'), 403);

        $ids = explode(',', $request->input('ids', ''));
        $ids = array_filter($ids);
        if (empty($ids)) {
            abort(400, 'No characteristics selected');
        }

        $specs = CharacteristicsTemplate::whereIn('id', $ids)->get();

        if ($specs->isEmpty()) {
            abort(404, 'Characteristics not found');
        }

        return Excel::download(
            new BulkCharacteristicsExport($specs),
            'คุณลักษณะ_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function comparisonPdf(Comparison $comparison)
    {
        abort_unless(auth()->user()->hasPermission('comparisons', 'export'), 403);

        $comparison->load('vendors');
        $spec = $comparison->characteristics_template_id
            ? CharacteristicsTemplate::find($comparison->characteristics_template_id)
            : null;

        $pdf = Pdf::loadView('exports.comparison-pdf', compact('comparison', 'spec'))
            ->setPaper('a4', 'landscape');

        $filename = 'เปรียบเทียบ_' . preg_replace('/[^\w\-]/', '_', $comparison->name) . '.pdf';

        return $pdf->download($filename);
    }

    public function specPdf(CharacteristicsTemplate $spec)
    {
        abort_unless(auth()->user()->hasPermission('specs', 'export'), 403);

        $pdf = Pdf::loadView('exports.spec-pdf', compact('spec'))
            ->setPaper('a4', 'portrait');

        $filename = 'คุณลักษณะ_' . preg_replace('/[^\w\-]/', '_', $spec->name) . '.pdf';

        return $pdf->download($filename);
    }

    public function sampleProductsTemplate()
    {
        abort_unless(auth()->user()->hasPermission('products', 'import'), 403);

        return Excel::download(
            new SampleProductsExport(),
            'ตัวอย่าง_สินค้า.xlsx'
        );
    }

    public function sampleCharacteristicsTemplate()
    {
        abort_unless(auth()->user()->hasPermission('specs', 'import'), 403);

        return Excel::download(
            new SampleCharacteristicsExport(),
            'ตัวอย่าง_คุณลักษณะ.xlsx'
        );
    }
}
