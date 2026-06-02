<?php

namespace Database\Seeders;

use App\Models\Comparison;
use Illuminate\Database\Seeder;

class ComparisonSeeder extends Seeder
{
    public function run(): void
    {
        $cmp = Comparison::updateOrCreate(
            ['id' => 'cmp-001'],
            [
                'name' => 'เปรียบเทียบ Notebook สำนักงาน ปี 2569',
                'category' => 'Notebook',
                'year' => '2569', 'month' => '05',
                'characteristics_template_id' => 'sp-001',
                'notes' => 'เปรียบเทียบราคาสำหรับจัดซื้อ Notebook จำนวน 10 เครื่อง',
                'status' => 'draft',
                'created_date' => '2569-05-21', 'created_by' => 'admin',
            ]
        );

        $vendors = [
            [
                'position' => 1, 'name' => 'บริษัท เอบีซี จำกัด',
                'brand' => 'ASUS', 'model' => 'Vivobook 16 (X1607CA-MB535WA)', 'price' => 25900,
                'specs' => [
                    'Processor' => 'Intel Core Ultra 5 225H (1.7GHz, up to 4.9GHz)',
                    'Main Memory' => '16GB DDR5 Onboard',
                    'Storage' => '512GB M.2 NVMe PCIe 4.0 SSD',
                    'Display Screen' => '16.0" 1920×1200 WUXGA IPS 60Hz',
                    'OS' => 'Windows 11 Home + Microsoft Office Home 2024',
                    'Wireless' => 'Wireless 802.11ax (Wi-Fi 6)',
                    'Battery' => '42WHrs, 3-cell Li-ion',
                    'Net Weight (kg)' => '1.88 kg',
                    'warranty' => '1 ปี ศูนย์บริการในประเทศ',
                ],
            ],
            [
                'position' => 2, 'name' => 'ห้างหุ้นส่วนจำกัด ดีอีเอฟ',
                'brand' => 'HP', 'model' => 'Laptop 15s-fq5330TU', 'price' => 24500,
                'specs' => [
                    'Processor' => 'Intel Core i5-1235U (1.3GHz, up to 4.4GHz)',
                    'Main Memory' => '8GB DDR4',
                    'Storage' => '512GB PCIe NVMe SSD',
                    'Display Screen' => '15.6" 1920×1080 FHD IPS',
                    'OS' => 'Windows 11 Home',
                    'Wireless' => 'Wireless 802.11ax (Wi-Fi 6)',
                    'Battery' => '41Wh, 3-cell',
                    'Net Weight (kg)' => '1.69 kg',
                    'warranty' => '1 ปี ศูนย์บริการในประเทศ',
                ],
            ],
            [
                'position' => 3, 'name' => 'บริษัท จีเอชไอ จำกัด',
                'brand' => 'Lenovo', 'model' => 'IdeaPad 5 15IAL7', 'price' => 26900,
                'specs' => [
                    'Processor' => 'Intel Core i5-1235U (1.3GHz, up to 4.4GHz)',
                    'Main Memory' => '16GB DDR5',
                    'Storage' => '512GB SSD NVMe',
                    'Display Screen' => '15.6" 1920×1080 FHD IPS',
                    'OS' => 'Windows 11 Home + Office Home',
                    'Wireless' => 'Wi-Fi 6 802.11ax',
                    'Battery' => '57Wh, 3-cell',
                    'Net Weight (kg)' => '1.70 kg',
                    'warranty' => '2 ปี ศูนย์บริการในประเทศ',
                ],
            ],
        ];

        $cmp->vendors()->delete();
        foreach ($vendors as $v) {
            $cmp->vendors()->create($v);
        }
    }
}
