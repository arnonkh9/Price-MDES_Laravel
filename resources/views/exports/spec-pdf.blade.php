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
        font-size: 10pt;
        color: #1e293b;
        background: #fff;
    }
    .page { padding: 20mm 18mm 18mm 18mm; }

    /* Header */
    .doc-header { border-bottom: 2px solid #7C3AED; padding-bottom: 10px; margin-bottom: 16px; }
    .doc-title { font-size: 16pt; font-weight: bold; color: #7C3AED; }
    .doc-subtitle { font-size: 9pt; color: #64748b; margin-top: 3px; }

    /* Meta grid */
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .meta-table td { padding: 3px 8px 3px 0; font-size: 9pt; width: 25%; }
    .meta-label { color: #64748b; }
    .meta-value { font-weight: bold; }

    /* Spec table */
    table.spec-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
    }
    table.spec-table th {
        background: #7C3AED;
        color: white;
        padding: 7px 10px;
        text-align: left;
        font-weight: bold;
    }
    table.spec-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    table.spec-table tr:nth-child(even) td { background: #f5f3ff; }
    table.spec-table td.field-name {
        font-weight: 600;
        color: #5b21b6;
        width: 35%;
    }
    table.spec-table td.field-value { width: 65%; }

    /* Footer */
    .footer {
        margin-top: 16px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        font-size: 8pt;
        color: #94a3b8;
        text-align: right;
    }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="doc-header">
        <div class="doc-title">ข้อกำหนดคุณลักษณะพื้นฐาน</div>
        <div class="doc-subtitle">{{ $spec->name }}</div>
    </div>

    {{-- Meta --}}
    @php $fmt = fn($n) => $n ? number_format((float)$n) : '-'; @endphp
    <table class="meta-table">
        <tr>
            <td><span class="meta-label">ประเภทสินค้า: </span><span class="meta-value">{{ \App\Support\Specs::label($spec->category) }}</span></td>
            <td><span class="meta-label">ปี พ.ศ.: </span><span class="meta-value">{{ $spec->year ?: '-' }}</span></td>
            <td><span class="meta-label">เดือน: </span><span class="meta-value">{{ $spec->month ? \App\Support\Specs::monthLabel($spec->month) : '-' }}</span></td>
            <td><span class="meta-label">วงเงิน: </span><span class="meta-value">{{ $fmt($spec->budget) }} บาท</span></td>
        </tr>
        <tr>
            <td><span class="meta-label">สร้างโดย: </span><span class="meta-value">{{ $spec->created_by ?: '-' }}</span></td>
            <td><span class="meta-label">วันที่: </span><span class="meta-value">{{ $spec->created_date ?: '-' }}</span></td>
            <td colspan="2"><span class="meta-label">วัตถุประสงค์: </span><span class="meta-value">{{ $spec->purpose ?: '-' }}</span></td>
        </tr>
    </table>

    {{-- Spec items table --}}
    @if (!empty($spec->specs))
    <table class="spec-table">
        <thead>
            <tr>
                <th>คุณลักษณะ / ข้อกำหนด</th>
                <th>รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($spec->specs as $key => $value)
            <tr>
                <td class="field-name">{{ $key }}</td>
                <td class="field-value">{{ \App\Support\Specs::display($value) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#94a3b8; font-size:9pt; margin-top:12px;">ไม่มีข้อกำหนดคุณลักษณะ</p>
    @endif

    {{-- Footer --}}
    <div class="footer">
        พิมพ์โดยระบบราคากลาง | {{ now()->format('d/m/') . (now()->year + 543) }}
    </div>
</div>
</body>
</html>
