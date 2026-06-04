<!DOCTYPE html>
<html lang="th">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    @font-face {
        font-family: 'Tahoma';
        font-style: normal;
        font-weight: normal;
        src: url('{{ storage_path("fonts/tahoma.ttf") }}') format('truetype');
    }
    @font-face {
        font-family: 'Tahoma';
        font-style: normal;
        font-weight: bold;
        src: url('{{ storage_path("fonts/tahomabd.ttf") }}') format('truetype');
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Tahoma', 'DejaVu Sans', sans-serif;
        font-size: 9pt;
        color: #1e293b;
        background: #fff;
    }
    .page { padding: 16mm 14mm 14mm 14mm; }

    .doc-header { border-bottom: 2px solid #1B3A6B; padding-bottom: 10px; margin-bottom: 12px; }
    .doc-title { font-size: 15pt; font-weight: bold; color: #1B3A6B; }
    .doc-subtitle { font-size: 9pt; color: #64748b; margin-top: 3px; }

    .meta { font-size: 8.5pt; color: #475569; margin-bottom: 12px; }
    .meta strong { color: #1e293b; }

    /* KPI strip */
    .kpis { display: table; width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px 0; }
    .kpi { display: table-cell; background: #f1f5f9; border-radius: 4px; padding: 8px 10px; width: 25%; }
    .kpi-value { font-size: 13pt; font-weight: bold; color: #1B3A6B; }
    .kpi-label { font-size: 8pt; color: #64748b; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
    th {
        background: #1B3A6B; color: white; padding: 6px 7px;
        text-align: left; font-weight: bold;
    }
    th.right, td.right { text-align: right; }
    th.center, td.center { text-align: center; }
    td { padding: 4px 7px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }

    .footer {
        margin-top: 14px; padding-top: 8px; border-top: 1px solid #e2e8f0;
        font-size: 8pt; color: #94a3b8; text-align: right;
    }
    .empty { padding: 20px; text-align: center; color: #94a3b8; }
</style>
</head>
<body>
<div class="page">

    <div class="doc-header">
        <div class="doc-title">{{ $report['title'] }}</div>
        <div class="doc-subtitle">ระบบบริหารจัดการราคากลาง</div>
    </div>

    <div class="meta">
        <strong>ปี:</strong> {{ $filters['year'] === 'all' ? 'ทุกปี' : $filters['year'] }}
        @if (($filters['category'] ?? 'all') !== 'all')
            &nbsp;|&nbsp; <strong>หมวดหมู่:</strong> {{ \App\Support\Specs::label($filters['category']) }}
        @endif
        &nbsp;|&nbsp; <strong>จำนวน:</strong> {{ number_format(count($report['rows'])) }} รายการ
    </div>

    {{-- KPIs --}}
    <div class="kpis">
        @foreach ($report['kpis'] as $kpi)
            <div class="kpi">
                <div class="kpi-value">{{ $kpi['value'] }}</div>
                <div class="kpi-label">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                @foreach ($report['columns'] as $col)
                    <th class="{{ $col['align'] ?? 'left' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($report['columns'] as $col)
                        <td class="{{ $col['align'] ?? 'left' }}">{{ $row[$col['key']] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($report['columns']) }}">ไม่มีข้อมูลตามเงื่อนไขที่เลือก</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        พิมพ์โดยระบบราคากลาง | {{ now()->format('d/m/') . (now()->year + 543) }}
    </div>
</div>
</body>
</html>
