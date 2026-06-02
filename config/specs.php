<?php

// Constants ported from the design prototype (src/data.js + SpecManager.jsx).
// Spec field definitions are static config (not stored in the database).

return [

    // Categories (seed source). slug => meta
    'categories' => [
        ['id' => 'Notebook',          'label' => 'Notebook',       'short' => 'NB',   'color' => '#2563EB'],
        ['id' => 'AIO',               'label' => 'All-in-One',     'short' => 'AIO',  'color' => '#7C3AED'],
        ['id' => 'AI-COM',            'label' => 'AI Computer',    'short' => 'AI',   'color' => '#DB2777'],
        ['id' => 'PC',                'label' => 'Desktop PC',     'short' => 'PC',   'color' => '#059669'],
        ['id' => 'gaming-desktop-pc', 'label' => 'Gaming Desktop', 'short' => 'GAME', 'color' => '#DC2626'],
        ['id' => 'mini-pc',           'label' => 'Mini PC',        'short' => 'MINI', 'color' => '#EA580C'],
        ['id' => 'Server',            'label' => 'Server',         'short' => 'SVR',  'color' => '#0369A1'],
        ['id' => 'monitor',           'label' => 'Monitor',        'short' => 'MON',  'color' => '#3B82F6'],
        ['id' => 'ups',               'label' => 'UPS',            'short' => 'UPS',  'color' => '#F59E0B'],
        ['id' => 'software',          'label' => 'Software',       'short' => 'SW',   'color' => '#10B981'],
        ['id' => 'projector',         'label' => 'Projector',      'short' => 'PRJ',  'color' => '#8B5CF6'],
        ['id' => 'printer',           'label' => 'Printer',        'short' => 'PRT',  'color' => '#EF4444'],
        ['id' => 'network',           'label' => 'Network',        'short' => 'NET',  'color' => '#06B6D4'],
    ],

    // 12 preset colors offered in the Category Manager.
    'palette' => [
        '#2563EB', '#7C3AED', '#DB2777', '#059669', '#DC2626', '#EA580C',
        '#0369A1', '#0891B2', '#CA8A04', '#65A30D', '#9333EA', '#475569',
    ],

    // Spec groups + fields (order matters; matches prototype SPEC_GROUPS).
    'groups' => [
        [
            'id' => 'spec', 'label' => 'ข้อมูลจำเพาะ',
            'fields' => [
                '1', '2', '3', '4', '5',
                '6', '7', '8', '9', '10',
                '11', '12', '13', '14', '15',
            ],
        ],
    ],

    // Thai month abbreviations (index 0 = January).
    'months' => ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],

    // Buddhist-era year options for forms/filters.
    'years' => ['2566', '2567', '2568', '2569', '2570', '2571'],

    // History import-source options (product detail history form).
    'history_sources' => ['Excel', 'กรอกด้วยมือ', 'ดาวน์โหลดจากเว็บ', 'API / ระบบอัตโนมัติ', 'อื่นๆ'],
];
