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
    .doc-header { border-bottom: 2px solid #1B3A6B; padding-bottom: 10px; margin-bottom: 16px; }
    .doc-title { font-size: 16pt; font-weight: bold; color: #1B3A6B; }
    .doc-subtitle { font-size: 9pt; color: #64748b; margin-top: 3px; }

    /* Meta grid */
    .meta-grid { display: table; width: 100%; margin-bottom: 14px; }
    .meta-row { display: table-row; }
    .meta-cell { display: table-cell; padding: 3px 8px 3px 0; font-size: 9pt; width: 25%; }
    .meta-label { color: #64748b; }
    .meta-value { font-weight: bold; }

    /* Spec reference box */
    .spec-box {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 14px;
        font-size: 9pt;
    }
    .spec-box-title { font-weight: bold; color: #0369a1; margin-bottom: 4px; }

    /* Main comparison table */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    th {
        background: #1B3A6B;
        color: white;
        padding: 7px 8px;
        text-align: left;
        font-weight: bold;
        font-size: 9pt;
    }
    th.vendor-col { text-align: center; }
    td {
        padding: 5px 8px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    tr.section-header td {
        background: #f1f5f9;
        font-weight: bold;
        color: #475569;
        font-size: 8.5pt;
        padding: 4px 8px;
    }
    tr.price-row td {
        background: #fefce8;
        font-weight: bold;
    }
    tr:nth-child(even) td { background: #f8fafc; }
    tr.price-row td { background: #fefce8 !important; }
    td.vendor-val { text-align: center; }
    td.spec-ref { color: #0369a1; font-style: italic; }

    /* Winner highlight */
    .winner { color: #16a34a; font-weight: bold; }

    /* Footer */
    .footer {
        margin-top: 16px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        font-size: 8pt;
        color: #94a3b8;
        text-align: right;
    }

    /* Notes */
    .notes-box {
        margin-top: 12px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 9pt;
    }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="doc-header">
        <div class="doc-title">ตารางเปรียบเทียบราคาและคุณลักษณะ</div>
        <div class="doc-subtitle">{{ $comparison->name }}</div>
    </div>

    {{-- Meta information --}}
    <div class="meta-grid">
        <div class="meta-row">
            <div class="meta-cell"><span class="meta-label">ประเภทสินค้า: </span><span class="meta-value">{{ \App\Support\Specs::label($comparison->category) }}</span></div>
            <div class="meta-cell"><span class="meta-label">ปี พ.ศ.: </span><span class="meta-value">{{ $comparison->year ?: '-' }}</span></div>
            <div class="meta-cell"><span class="meta-label">เดือน: </span><span class="meta-value">{{ $comparison->month ? \App\Support\Specs::monthLabel($comparison->month) : '-' }}</span></div>
            <div class="meta-cell"><span class="meta-label">วันที่สร้าง: </span><span class="meta-value">{{ $comparison->created_date ?: '-' }}</span></div>
        </div>
        <div class="meta-row">
            <div class="meta-cell"><span class="meta-label">สร้างโดย: </span><span class="meta-value">{{ $comparison->created_by ?: '-' }}</span></div>
            <div class="meta-cell"><span class="meta-label">สถานะ: </span><span class="meta-value">{{ $comparison->status === 'final' ? 'อนุมัติแล้ว' : 'ร่าง' }}</span></div>
            <div class="meta-cell"></div>
            <div class="meta-cell"></div>
        </div>
    </div>

    {{-- Spec reference --}}
    @if ($spec)
    <div class="spec-box">
        <div class="spec-box-title">คุณลักษณะพื้นฐานอ้างอิง: {{ $spec->name }}</div>
        <div>วงเงิน: {{ $spec->budget ? number_format((float)$spec->budget) . ' บาท' : '-' }}
             | วัตถุประสงค์: {{ $spec->purpose ?: '-' }}</div>
    </div>
    @endif

    {{-- Comparison table --}}
    @php
        $v = $comparison->vendors->values();
        $fmt = fn($n) => $n ? number_format((float)$n) : '-';
        $vendor = fn($i) => $v->get($i);
        $prices = $v->map(fn($vd) => (float)$vd->price)->filter(fn($p) => $p > 0);
        $minPrice = $prices->min();
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:28%">รายการ</th>
                @if ($spec)<th style="width:18%; text-align:center;">คุณลักษณะพื้นฐาน</th>@endif
                <th class="vendor-col" style="width:{{ $spec ? '18%' : '24%' }}">{{ $vendor(0)?->name ?: 'เจ้าที่ 1' }}</th>
                <th class="vendor-col" style="width:{{ $spec ? '18%' : '24%' }}">{{ $vendor(1)?->name ?: 'เจ้าที่ 2' }}</th>
                <th class="vendor-col" style="width:{{ $spec ? '18%' : '24%' }}">{{ $vendor(2)?->name ?: 'เจ้าที่ 3' }}</th>
            </tr>
        </thead>
        <tbody>
            {{-- Basic info rows --}}
            <tr>
                <td><strong>แบรนด์</strong></td>
                @if ($spec)<td class="spec-ref">-</td>@endif
                <td class="vendor-val">{{ $vendor(0)?->brand ?: '-' }}</td>
                <td class="vendor-val">{{ $vendor(1)?->brand ?: '-' }}</td>
                <td class="vendor-val">{{ $vendor(2)?->brand ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>รุ่น / โมเดล</strong></td>
                @if ($spec)<td class="spec-ref">-</td>@endif
                <td class="vendor-val">{{ $vendor(0)?->model ?: '-' }}</td>
                <td class="vendor-val">{{ $vendor(1)?->model ?: '-' }}</td>
                <td class="vendor-val">{{ $vendor(2)?->model ?: '-' }}</td>
            </tr>
            <tr class="price-row">
                <td><strong>ราคาเสนอ (บาท)</strong></td>
                @if ($spec)<td class="spec-ref" style="text-align:center;">วงเงิน {{ $fmt($spec->budget) }} ฿</td>@endif
                <td class="vendor-val">
                    {{ $fmt($vendor(0)?->price) }}
                    @if ($vendor(0) && (float)$vendor(0)->price === (float)$minPrice && $minPrice > 0)
                        <span class="winner"> ✓</span>
                    @endif
                </td>
                <td class="vendor-val">
                    {{ $fmt($vendor(1)?->price) }}
                    @if ($vendor(1) && (float)$vendor(1)->price === (float)$minPrice && $minPrice > 0)
                        <span class="winner"> ✓</span>
                    @endif
                </td>
                <td class="vendor-val">
                    {{ $fmt($vendor(2)?->price) }}
                    @if ($vendor(2) && (float)$vendor(2)->price === (float)$minPrice && $minPrice > 0)
                        <span class="winner"> ✓</span>
                    @endif
                </td>
            </tr>

            {{-- Spec rows --}}
            @php
                $specKeys = \App\Support\Specs::comparisonFieldKeys($spec?->specs, $v->pluck('specs'));
                $activeKeys = collect($specKeys)->filter(function($f) use ($spec, $v) {
                    if ($spec && !empty($spec->specs[$f] ?? null)) return true;
                    return $v->contains(fn($vd) => !empty($vd->specs[$f] ?? null));
                });
            @endphp
            @if ($activeKeys->isNotEmpty())
                <tr class="section-header">
                    <td colspan="{{ $spec ? 5 : 4 }}">ข้อมูลจำเพาะ (Specifications)</td>
                </tr>
                @foreach ($activeKeys as $field)
                <tr>
                    <td>{{ $field }}</td>
                    @if ($spec)<td class="spec-ref" style="text-align:center;">{{ \App\Support\Specs::display($spec->specs[$field] ?? null) }}</td>@endif
                    <td class="vendor-val">{{ \App\Support\Specs::display($vendor(0)?->specs[$field] ?? null) }}</td>
                    <td class="vendor-val">{{ \App\Support\Specs::display($vendor(1)?->specs[$field] ?? null) }}</td>
                    <td class="vendor-val">{{ \App\Support\Specs::display($vendor(2)?->specs[$field] ?? null) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- Notes --}}
    @if ($comparison->notes)
    <div class="notes-box">
        <strong>หมายเหตุ:</strong> {{ $comparison->notes }}
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        พิมพ์โดยระบบราคากลาง | {{ now()->format('d/m/') . (now()->year + 543) }}
    </div>
</div>
</body>
</html>
