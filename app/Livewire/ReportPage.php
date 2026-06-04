<?php

namespace App\Livewire;

use App\Support\Reports;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('รายงาน | ระบบราคากลาง')]
class ReportPage extends Component
{
    public string $reportType = 'price';
    public string $year = 'all';
    public string $category = 'all';

    public function mount(): void
    {
        abort_unless(auth()->user()->canSeeMenu('reports'), 403);
    }

    public function setType(string $type): void
    {
        if (Reports::isValidType($type)) {
            $this->reportType = $type;
        }
    }

    public function render()
    {
        if (! Reports::isValidType($this->reportType)) {
            $this->reportType = 'price';
        }

        $filters = ['year' => $this->year, 'category' => $this->category];
        $report  = Reports::build($this->reportType, $filters);

        return view('livewire.report-page', [
            'types'      => Reports::types(),
            'report'     => $report,
            'categories' => Specs::categories(),
            'years'      => Specs::years(),
            'canExport'  => auth()->user()->hasPermission('reports', 'view'),
        ]);
    }
}
