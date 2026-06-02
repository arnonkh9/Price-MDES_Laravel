<?php

namespace Database\Seeders;

use App\Models\CharacteristicsTemplate;
use Illuminate\Database\Seeder;

class CharacteristicsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $specs = [
            [
                'id' => 'sp-001',
                'name' => 'Notebook สำหรับงานสำนักงาน',
                'category' => 'Notebook',
                'purpose' => 'คุณลักษณะพื้นฐานสำหรับจัดซื้อ Notebook ใช้งานสำนักงานทั่วไป ประจำปี 2569',
                'budget' => 30000,
                'year' => '2569', 'month' => '05',
                'created_date' => '2569-05-21', 'created_by' => 'admin',
                'specs' => [
                    'Processor' => 'Intel Core i5 หรือสูงกว่า (Gen 12 ขึ้นไป) หรือ AMD Ryzen 5 เทียบเท่า',
                    'Main Memory' => 'ไม่น้อยกว่า 16GB DDR5',
                    'Storage' => 'ไม่น้อยกว่า 512GB SSD (NVMe PCIe)',
                    'Display Screen' => 'ขนาดไม่น้อยกว่า 14 นิ้ว ความละเอียดไม่น้อยกว่า Full HD (1920×1080)',
                    'OS' => 'Windows 11 Home หรือสูงกว่า พร้อม Microsoft Office',
                    'Wireless' => 'รองรับ Wi-Fi 6 (802.11ax) ขึ้นไป',
                    'Bluetooth' => 'Bluetooth 5.0 ขึ้นไป',
                    'Battery' => 'ความจุไม่น้อยกว่า 40WHrs',
                    'Web Camera' => 'กล้อง HD 720p ขึ้นไป',
                    'Net Weight (kg)' => 'น้ำหนักไม่เกิน 2.5 กิโลกรัม (รวมแบตเตอรี่)',
                    'warranty' => 'รับประกันไม่น้อยกว่า 1 ปี ศูนย์บริการในประเทศไทย',
                ],
                'history' => ['date' => '2569-05-21', 'user' => 'admin', 'action' => 'สร้างคุณลักษณะพื้นฐานใหม่', 'detail' => 'ตั้งค่าเริ่มต้น'],
            ],
            [
                'id' => 'sp-002',
                'name' => 'Desktop PC สำหรับงานทั่วไป',
                'category' => 'PC',
                'purpose' => 'คุณลักษณะพื้นฐานสำหรับจัดซื้อ Desktop PC งานธุรการและงานเอกสาร',
                'budget' => 25000,
                'year' => '2569', 'month' => '05',
                'created_date' => '2569-05-21', 'created_by' => 'admin',
                'specs' => [
                    'Processor' => 'Intel Core i5 หรือสูงกว่า หรือ AMD Ryzen 5 เทียบเท่า',
                    'Main Memory' => 'ไม่น้อยกว่า 16GB DDR5',
                    'Storage' => 'ไม่น้อยกว่า 512GB SSD (NVMe)',
                    'OS' => 'Windows 11 Home หรือสูงกว่า พร้อม Microsoft Office',
                    'Wireless' => 'รองรับ Wi-Fi 6 (802.11ax) ขึ้นไป',
                    'Network' => 'รองรับ Gigabit Ethernet',
                    'Power Supply' => 'กำลังไฟไม่น้อยกว่า 180W มาตรฐาน 80+ Bronze',
                    'warranty' => 'รับประกันไม่น้อยกว่า 1 ปี ศูนย์บริการในประเทศไทย',
                ],
                'history' => ['date' => '2569-05-21', 'user' => 'admin', 'action' => 'สร้างคุณลักษณะพื้นฐานใหม่', 'detail' => 'ตั้งค่าเริ่มต้น'],
            ],
        ];

        foreach ($specs as $s) {
            $history = $s['history'];
            unset($s['history']);
            $spec = CharacteristicsTemplate::updateOrCreate(['id' => $s['id']], $s);
            $spec->histories()->delete();
            $spec->histories()->create($history);
        }
    }
}
