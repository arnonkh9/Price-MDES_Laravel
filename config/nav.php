<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu Visibility by Role
    |--------------------------------------------------------------------------
    |
    | Map: route name → roles ที่สามารถมองเห็นเมนูนั้นได้
    | Routes ที่ไม่ได้กำหนด = ทุก role มองเห็น
    |
    | วิธีปรับ:
    |   - เพิ่ม role ใน array → role นั้นเห็นเมนูด้วย
    |   - ลบ route ออกจาก list → ทุก role เห็น
    |   - เปลี่ยน array ให้เหลือแค่ ['admin'] → admin เท่านั้น
    |
    */
    'menu_visibility' => [
        // เมนูที่ viewer ไม่เห็น (เห็นได้เฉพาะ admin และ editor)
        'specs'           => ['admin', 'editor'],
        'comparisons'     => ['admin', 'editor'],
        'guidelines'      => ['admin', 'editor'],
        'recommendations' => ['admin', 'editor'],

        // เมนูที่ admin เท่านั้นเห็น (จัดการระบบ)
        'users'           => ['admin'],
        'categories'      => ['admin'],
        'brands'          => ['admin'],
    ],
];
