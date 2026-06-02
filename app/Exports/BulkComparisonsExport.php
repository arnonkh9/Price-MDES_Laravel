<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BulkComparisonsExport implements WithMultipleSheets
{
    public function __construct(private Collection $comparisons) {}

    public function sheets(): array
    {
        return $this->comparisons->map(function ($cmp) {
            return new ComparisonExport($cmp);
        })->all();
    }
}
