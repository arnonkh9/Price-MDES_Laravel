<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->products() as $p) {
            $product = Product::updateOrCreate(
                ['id' => $p['id']],
                [
                    'category' => $p['category'],
                    'brand' => $p['brand'],
                    'model' => $p['model'],
                    'price' => $p['price'],
                    'price_unit' => $p['priceUnit'],
                    'price_date' => $p['priceDate'],
                    'specs' => $p['specs'],
                ]
            );

            $product->histories()->delete();
            foreach ($p['editHistory'] as $h) {
                $product->histories()->create($h);
            }
        }
    }

    private function products(): array
    {
        $hist = fn () => [['date' => '2569-05-21', 'user' => 'admin', 'action' => 'เพิ่มข้อมูลใหม่', 'detail' => 'นำเข้าจากไฟล์ Excel']];
        $hist23 = fn () => [['date' => '2569-05-23', 'user' => 'admin', 'action' => 'เพิ่มข้อมูลตัวอย่าง', 'detail' => 'เพิ่มข้อมูลตัวอย่างสินค้า']];

        return [
            // Original 7 products
            [
                'id' => 'nb-001', 'category' => 'Notebook', 'brand' => 'ASUS',
                'model' => 'Vivobook 16 (X1607CA-MB535WA)',
                'price' => 25900, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => 'Intel Core Ultra 5 225H (1.7GHz, up to 4.9GHz, 14C/14T, 18MB Intel Smart Cache)',
                    'Chipset' => 'Intel SoC Platform',
                    'CPU Series' => 'Core Ultra 5',
                    'AI Engine' => 'Intel AI Boost',
                    'Graphics' => 'Intel Arc Graphics (Integrated Graphics)',
                    'Display Screen' => '16.0" 1920x1200 (WUXGA) 16:10, IPS, 60Hz, 300nits, Anti-glare, 45% NTSC',
                    'Main Memory' => '16GB DDR5 Onboard memory',
                    'Memory Slot' => '1x DDR5 SO-DIMM slot',
                    'Max Memory' => 'Up to 32GB',
                    'Storage' => '512GB M.2 NVMe PCIe 4.0 SSD',
                    'Storage Slot' => '1x M.2 2280 PCIe 4.0x4 (Occupied)',
                    'Optical Disk Drive' => 'None',
                    'Web Camera' => 'FHD camera with IR function to support Windows Hello; With privacy shutter',
                    'Sound Technology' => 'SonicMaster',
                    'Audio Jack' => '1x 3.5mm Combo Audio Jack',
                    'Speaker' => "• Built-in speaker\n• Built-in array microphone",
                    'OS' => 'Windows 11 Home + Microsoft Office Home 2024 + Microsoft 365 Basic',
                    'Wireless' => 'Wireless 802.11ax (Wi-Fi 6)',
                    'Bluetooth' => 'Bluetooth 5.2',
                    'Ports' => "2x USB 3.2 Gen 1 Type-A\n2x USB 3.2 Gen 1 Type-C with display / power delivery\n1x HDMI 1.4",
                    'Network' => 'N/A',
                    'CardReader' => 'N/A',
                    'Power Adapter' => 'TYPE-C, 65W AC Adapter, Output: 20V DC, 3.25A',
                    'Battery' => '42WHrs, 3S1P, 3-cell Li-ion',
                    'Dimension (cm)' => '(W x L x H) : 35.70 x 25.06 x 1.99 cm',
                    'Net Weight (kg)' => '1.88 kg',
                    'Package Dimension (cm)' => '(W x L x H) : 13.00 x 49.00 x 31.00 cm',
                    'Gross Weight (kg)' => '4.00 kg',
                    'Volume (cm³)' => '19,747.00 cm³',
                    'Keyboard Type' => 'ENG/TH Backlit Chiclet Keyboard with Num-key',
                    'Backlit' => 'Yes',
                    'Touchpad' => 'Precision touchpad',
                    'warranty' => "• LCD cover-material: Plastic\n• Top case-material: Plastic\n• Bottom case-material: Plastic",
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'aio-001', 'category' => 'AIO', 'brand' => 'HP',
                'model' => 'HP 24-ct2006d (C8BZ9PA#AKL)',
                'price' => 28500, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => 'AMD Ryzen AI 5 330 Processor (2.0GHz up to 4.5GHz, 4C/8T)',
                    'Chipset' => 'AMD SoC Platform',
                    'CPU Series' => 'Ryzen AI 5',
                    'AI Engine' => 'AMD Ryzen AI (NPU 50 TOPS)',
                    'Graphics' => 'AMD Radeon 820M Graphics (Integrated Graphics)',
                    'Display Screen' => '23.8" 1920x1080 (FHD), IPS, three-sided micro-edge, anti-glare, 250 nits, 99% sRGB',
                    'Main Memory' => '16GB (2x 8GB) DDR5-5600 SO-DIMM',
                    'Memory Slot' => '2x DDR5 SO-DIMM slots',
                    'Storage' => '512GB PCIe NVMe Value M.2 2280 SSD',
                    'Storage Slot' => '1x M.2 SSD (Occupied)',
                    'Web Camera' => 'HP True Vision 1080p FHD IR tilt privacy camera with dual array digital microphones',
                    'Audio' => 'HD Audio',
                    'Speaker' => 'Dual 2W speakers',
                    'OS' => 'Windows 11 Home + Microsoft Office Home 2024',
                    'Wireless' => 'Wireless 802.11ax (Wi-Fi 6)',
                    'Bluetooth' => 'Bluetooth 5.4',
                    'Ports' => "1x USB Type-C 5Gbps\n2x USB Type-A 5Gbps\n2x USB 2.0 Type-A\n1x HDMI-out 1.4\n1x headphone/microphone combo",
                    'Network' => '10/100/1000 LAN',
                    'Power Adapter' => '90W Smart AC power adapter',
                    'Dimension (cm)' => '(W x L x H) : 54.05 x 18.63 x 48.49 cm',
                    'Net Weight (kg)' => '5.53 kg',
                    'Package Dimension (cm)' => '(W x L x H) : 17.50 x 64.00 x 46.00 cm',
                    'Gross Weight (kg)' => '9.30 kg',
                    'Volume (cm³)' => '51,520.00 cm³',
                    'Keyboard Type' => 'ENG/TH Keyboard',
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'ai-001', 'category' => 'AI-COM', 'brand' => 'ASUS',
                'model' => 'NVIDIA DGX Spark Ascent GX10-GG0011BN',
                'price' => 189000, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => '20-core Arm: 10 Cortex-X925 + 10 Cortex-A725',
                    'Chipset' => 'NVIDIA® GB10 Grace Blackwell Superchip',
                    'CPU Series' => 'NVIDIA Blackwell Architecture',
                    'AI Performance' => '• Up to 1 petaFLOP of AI performance using FP4',
                    'Main Memory' => '128GB LPDDR5x unified system memory',
                    'Storage' => '1TB M.2 NVMe PCIe 4.0 SSD',
                    'Storage Slot' => '1x M.2 2242/2230 SSD (Occupied)',
                    'Audio' => 'N/A',
                    'OS' => 'NVIDIA DGX OS',
                    'Special Feature' => 'NVIDIA DGX Spark – Professional AI Workstation',
                    'Wireless' => 'AW-EM637 Wi-Fi 7 (Gig+) 2x2',
                    'Bluetooth' => 'Bluetooth® 5',
                    'Port' => "3x USB 3.2 Gen 2x2 Type-C (DisplayPort 2.1)\n1x USB 3.2 Gen 2x2 Type-C with PD in (180W EPR)\n1x HDMI 2.1\n1x Kensington Lock\n1x RJ-45 10GbE\n2x QSFP (ConnectX-7 Smart NIC)",
                    'Network' => '10 GbE + 2x QSFP ConnectX-7',
                    'Power Supply' => "• 240W Adapter\n• Power cord",
                    'Dimension (cm)' => '(W x L x H) : 15.00 x 15.00 x 5.10 cm',
                    'Net Weight (kg)' => '1.48 kg',
                    'Package Dimension (cm)' => '(W x L x H) : 19.00 x 15.00 x 19.00 cm',
                    'Gross Weight (kg)' => '3.19 kg',
                    'Volume (cm³)' => '5,415.00 cm³',
                    "What's in the box?" => "• ASUS Ascent GX10\n• AC Adapter\n• Power Cord\n• User Manual\n• Warranty Card",
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'pc-001', 'category' => 'PC', 'brand' => 'ASUS',
                'model' => 'ExpertCenter P700 SFF PM700SK-0R5330001WS',
                'price' => 22900, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => 'AMD Ryzen™AI 5 330 Processor (2.0GHz Up to 4.4GHz, 4C/8T, 8MB Cache)',
                    'Chipset' => 'AMD SoC Platform',
                    'AI Engine' => 'AMD Ryzen™ AI (NPU 50 TOPs)',
                    'Graphics' => 'Integrated: AMD Radeon Graphics',
                    'Main Memory' => '16GB DDR5-5600 SO-DIMM',
                    'Memory Slot' => '2x DDR5 SO-DIMM',
                    'Max Memory' => 'Up to 64GB',
                    'Storage' => '512GB M.2 2280 NVMe PCIe 4.0 SSD',
                    'Storage Slot' => "1x M.2 2280 connector (Occupied)\n1x 3.5\" Drive Bay\n1x M.2 connector for WiFi",
                    'Expansion Slots' => '1x PCIe® 4.0 x16 (operating at x4)',
                    'Audio Jack' => "High Definition 7.1 Channel Audio\n1x 3.5mm combo audio jack",
                    'OS' => 'Windows 11 Home + Microsoft Office Home 2024 + Microsoft 365 Basic',
                    'Wireless' => 'Wi-Fi 6 (802.11ax)',
                    'Bluetooth' => 'Bluetooth 5.2',
                    'Front I/O Port' => "1x USB 3.2 Gen 1 Type-C\n2x USB 3.2 Gen 1 Type-A",
                    'Rear I/O Port' => "1x USB 3.2 Gen 1 Type-A\n1x Displayport 1.4\n1x HDMI 2.1b",
                    'Network' => 'Gigabit Ethernet',
                    'CardReader' => '1x 2-in-1 card reader SD / MMC',
                    'Power Supply' => '180W power supply (80+ Bronze, peak 228W)',
                    'Dimension (cm)' => '(W x L x H) : 9.30 x 29.60 x 30.90 cm',
                    'Net Weight (kg)' => '5.06 kg',
                    'Keyboard' => 'ENG/TH Keyboard included',
                    'Mouse' => 'Mouse included',
                    'Volume (cm³)' => '40,800.00 cm³',
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'gpc-001', 'category' => 'gaming-desktop-pc', 'brand' => 'ASUS',
                'model' => 'TUF Gaming T500MV-13420H031WA',
                'price' => 35900, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => 'Intel Core i5-13420H (2.1GHz up to 4.6GHz, 12MB Intel Smart Cache)',
                    'Chipset' => 'Intel SoC Platform',
                    'CPU Series' => 'Core i5 13th Gen',
                    'Graphics' => 'NVIDIA GeForce RTX 3050 (6GB GDDR6): 1x DP, 1x HDMI, 1x DVI-D',
                    'Main Memory' => '32GB (2x 16GB) DDR5 SO-DIMM',
                    'Memory Slot' => '2x DDR5 SO-DIMM slot',
                    'Max Memory' => '64GB',
                    'Storage' => '512GB M.2 2280 NVMe PCIe 4.0 SSD',
                    'Storage Slot' => "2x M.2 2280 connector\n1x 3.5\" Drive Bay\n1x M.2 connector for WiFi",
                    'Expansion Slots' => '1x PCIe® 4.0 x16 (operating at x8)',
                    'Audio Jack' => "1x 7.1 channel audio (3 ports)\n1x 3.5mm combo audio jack",
                    'OS' => 'Windows 11 Home + Microsoft Office Home 2024 + Microsoft 365 Basic',
                    'Wireless' => 'Wi-Fi 6 (802.11ax) Dual band 2x2',
                    'Bluetooth' => 'Bluetooth 5.2',
                    'Front I/O Port' => "1x USB 3.2 Gen 1 Type-C\n2x USB 3.2 Gen 1 Type-A",
                    'Rear I/O Port' => "1x Displayport 1.4\n4x USB 2.0 Type-A",
                    'Network' => 'Gigabit Ethernet',
                    'Power Supply' => '330W power supply (80+ Platinum, peak 660W)',
                    'Dimension (cm)' => '(W x L x H) : 15.55 x 29.64 x 34.70 cm',
                    'Net Weight (kg)' => '5.90 kg',
                    'Package Dimension (cm)' => '(W x L x H) : 24.00 x 48.50 x 50.00 cm',
                    'Gross Weight (kg)' => '14.00 kg',
                    'Volume (cm³)' => '58,200.00 cm³',
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'mini-001', 'category' => 'mini-pc', 'brand' => 'ASUS',
                'model' => 'NUC 14 Essential Barebone RNUC14MNK1500000',
                'price' => 8900, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => 'Intel N150 Processor (Up to 3.6GHz, 6MB Intel Smart Cache, TDP 6W)',
                    'CPU Series' => 'Intel N-Series',
                    'Graphics' => 'Intel Graphics (Integrated)',
                    'Main Memory' => 'Memory Not Included',
                    'Memory Slot' => '1x DDR5 SO-DIMM slot',
                    'Max Memory' => 'Up to 16GB',
                    'Storage' => 'Storage Not Included',
                    'Storage Slot' => '1x M.2 2280/2242 PCIe Gen3x4 (128GB–2TB NVMe or SATA SSD)',
                    'Sound Technology' => 'Realtek ALC3251',
                    'Audio Jack' => '1x 3.5mm Headset Jack',
                    'OS' => 'DOS',
                    'Wireless' => 'Wireless 802.11ax',
                    'Bluetooth' => 'Bluetooth 5.3',
                    'Front I/O ports' => "1x USB 3.2 Gen 2 Type-C\n2x USB 3.2 Gen 2 Type-A",
                    'Back I/O ports' => "1x USB 3.2 Gen 2 Type-C w/ DisplayPort 1.4\n2x USB 3.2 Gen 2 Type-A\n1x USB 2.0 Type-A\n1x HDMI 2.1\n1x DisplayPort 1.4",
                    'Network' => '10/100/1000/2500 LAN',
                    'Power Adapter' => '19VDC, 3.42A, 65W Power Adapter',
                    'Dimension (cm)' => '(W x L x H) : 13.50 x 11.50 x 3.60 cm',
                    'Net Weight (kg)' => '0.48 kg',
                    'Package Dimension (cm)' => '(W x L x H) : 17.00 x 19.00 x 10.00 cm',
                    'Gross Weight (kg)' => '1.32 kg',
                    'Volume (cm³)' => '3,230.00 cm³',
                    'Special Feature' => 'VESA Bracket included',
                ],
                'editHistory' => $hist(),
            ],
            [
                'id' => 'svr-001', 'category' => 'Server', 'brand' => 'HPE',
                'model' => 'ProLiant DL380 Gen10 Plus (P05172-B21#UUF)',
                'price' => 285000, 'priceUnit' => 'บาท/เครื่อง',
                'priceDate' => '2569-05-21',
                'specs' => [
                    'Processor' => '2x Intel Xeon Gold 6326 (2.9GHz up to 3.5GHz, 24MB Cache)',
                    'Chipset' => 'Intel C621A',
                    'CPU Series' => 'Intel Xeon Gold',
                    'Main Memory' => '32GB (2x16GB) Dual Rank x8 DDR4-3200 CAS-22-22-22 Registered Smart Memory Kit',
                    'Storage' => '4 x 960GB SATA 6G Read Intensive SFF BC Multi Vendor SSD',
                    'Storage Slot' => '8 x SFF (Hot Plug) Chassis',
                    'Expansion Slots' => '1x HPE ProLiant DL380 Gen10 Plus x8/x16/x8 Primary FIO Riser Kit',
                    'Network' => 'Broadcom BCM57416 Ethernet 10Gb 2-port BASE-T OCP3 Adapter for HPE',
                    'Power Supply' => '2 x 800W Flex Slot Platinum Hot Plug Low Halogen Power Supply Kit',
                    'Dimension (cm)' => '(W x L x H) : 60.00 x 97.00 x 27.00 cm',
                    'Net Weight (kg)' => '29.16 kg',
                    'Volume (cm³)' => '157,140.00 cm³',
                    'RAID' => 'MegaRAID MR416i-a x16 Lanes 4GB Cache NVMe/SAS 12G Controller',
                    'System Management' => 'HPE iLO',
                    'Form Factor' => '2U Rack',
                    'Special Feature' => '1x HPE 96W Smart Storage Lithium-ion Battery with 145mm Cable Kit',
                ],
                'editHistory' => $hist(),
            ],

            // Additional Notebook products (nb-002 to nb-006)
            ['id' => 'nb-002', 'category' => 'Notebook', 'brand' => 'LENOVO', 'model' => 'ThinkBook Plus 15 Gen 2', 'price' => 28900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-14700H (2.3GHz, up to 5.6GHz, 20C/28T)', 'Main Memory' => '32GB DDR5 SODIMM', 'Storage' => '1TB M.2 NVMe PCIe 4.0 SSD', 'Display Screen' => '15.6" FHD (1920x1080) IPS 144Hz', 'Graphics' => 'NVIDIA GeForce RTX 4050 (6GB)', 'OS' => 'Windows 11 Pro', 'Battery' => '75Wh Li-polymer', 'Weight' => '1.98 kg'], 'editHistory' => $hist23()],
            ['id' => 'nb-003', 'category' => 'Notebook', 'brand' => 'HP', 'model' => 'Pavilion 15-eh3000au', 'price' => 22900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD Ryzen 5 7530U (2.8GHz, up to 4.6GHz)', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB SSD', 'Display Screen' => '15.6" FHD (1920x1080) IPS', 'Graphics' => 'AMD Radeon Graphics', 'OS' => 'Windows 11 Home', 'Battery' => '52.5Wh Li-ion', 'Weight' => '1.75 kg'], 'editHistory' => $hist23()],
            ['id' => 'nb-004', 'category' => 'Notebook', 'brand' => 'ACER', 'model' => 'Swift 3 SF314-55G', 'price' => 26900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-13700H (2.4GHz, up to 5.4GHz)', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB PCIe SSD', 'Display Screen' => '14.0" FHD (1920x1080) IPS 100% sRGB', 'Graphics' => 'NVIDIA GeForce RTX 4050 (6GB)', 'OS' => 'Windows 11 Home', 'Battery' => '60Wh Li-ion', 'Weight' => '1.4 kg'], 'editHistory' => $hist23()],
            ['id' => 'nb-005', 'category' => 'Notebook', 'brand' => 'Dell', 'model' => 'XPS 15 9530', 'price' => 38900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-13900HX (2.4GHz, up to 5.4GHz)', 'Main Memory' => '64GB DDR5 SODIMM', 'Storage' => '2TB M.2 NVMe PCIe 4.0 SSD', 'Display Screen' => '15.6" 4K+ (3840x2400) OLED Touch', 'Graphics' => 'NVIDIA RTX 4080 (12GB GDDR6)', 'OS' => 'Windows 11 Pro', 'Battery' => '86Wh Li-polymer', 'Weight' => '2.1 kg'], 'editHistory' => $hist23()],
            ['id' => 'nb-006', 'category' => 'Notebook', 'brand' => 'ASUS', 'model' => 'TUF Gaming A16 (2024)', 'price' => 34900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD Ryzen 9 7945HX3D (3.6GHz, up to 5.4GHz)', 'Main Memory' => '32GB DDR5', 'Storage' => '1TB M.2 PCIe 4.0 SSD', 'Display Screen' => '16" IPS (2560x1600) 240Hz', 'Graphics' => 'NVIDIA RTX 4070 (8GB GDDR6)', 'OS' => 'Windows 11 Home', 'Battery' => '90Wh Li-ion', 'Weight' => '2.1 kg'], 'editHistory' => $hist23()],

            // Additional AIO products (aio-002 to aio-006)
            ['id' => 'aio-002', 'category' => 'AIO', 'brand' => 'ASUS', 'model' => 'Vivo AiO M3702 (2024)', 'price' => 32900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD Ryzen 5 7530U', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB M.2 SSD', 'Display Screen' => '27" FHD (1920x1080) IPS', 'Graphics' => 'Radeon Graphics', 'Power Supply' => '180W Adapter', 'Connectivity' => 'Wi-Fi 6E, Bluetooth 5.3', 'Weight' => '4.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'aio-003', 'category' => 'AIO', 'brand' => 'Dell', 'model' => 'Inspiron 27 5000', 'price' => 26900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i5-13400', 'Main Memory' => '8GB DDR5', 'Storage' => '256GB SSD', 'Display Screen' => '27" FHD (1920x1080) VA', 'Graphics' => 'Intel UHD Graphics 730', 'Power Supply' => '150W External Adapter', 'Connectivity' => 'Wi-Fi 5, Bluetooth 5.0', 'Weight' => '5.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'aio-004', 'category' => 'AIO', 'brand' => 'ACER', 'model' => 'Aspire C27-1700', 'price' => 24900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i3-13100', 'Main Memory' => '8GB DDR5', 'Storage' => '512GB SSD', 'Display Screen' => '27" FHD (1920x1080) IPS', 'Graphics' => 'Intel UHD Graphics 730', 'Power Supply' => '120W Adapter', 'Connectivity' => 'Wi-Fi 5, Bluetooth 5.0', 'Weight' => '4.6 kg'], 'editHistory' => $hist23()],
            ['id' => 'aio-005', 'category' => 'AIO', 'brand' => 'Lenovo', 'model' => 'IdeaCentre 27IMB05 (2024)', 'price' => 30900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-13700T', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB M.2 SSD', 'Display Screen' => '27" FHD (1920x1080) IPS', 'Graphics' => 'Intel UHD Graphics 770', 'Power Supply' => '180W Adapter', 'Connectivity' => 'Wi-Fi 6, Bluetooth 5.0', 'Weight' => '5.3 kg'], 'editHistory' => $hist23()],
            ['id' => 'aio-006', 'category' => 'AIO', 'brand' => 'HP', 'model' => 'Pavilion 32 2024 Edition', 'price' => 45000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-13900', 'Main Memory' => '32GB DDR5', 'Storage' => '1TB M.2 SSD', 'Display Screen' => '32" 4K (3840x2160) IPS HDR', 'Graphics' => 'Intel UHD Graphics 770', 'Power Supply' => '240W External Adapter', 'Connectivity' => 'Wi-Fi 6E, Bluetooth 5.3', 'Weight' => '7.2 kg'], 'editHistory' => $hist23()],

            // Additional AI-COM products (ai-002 to ai-006)
            ['id' => 'ai-002', 'category' => 'AI-COM', 'brand' => 'Dell', 'model' => 'XPS 17 (AI Edition)', 'price' => 165000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-13900HX', 'Main Memory' => '48GB DDR5', 'Storage' => '2TB M.2 SSD', 'Display Screen' => '17" 4K+ OLED (3840x2400)', 'Graphics' => 'NVIDIA RTX 5880 Ada (48GB)', 'GPU Memory' => '48GB GDDR6', 'CUDA Cores' => '14080', 'OS' => 'Windows 11 Pro', 'Warranty' => '3 Years ProSupport Premium'], 'editHistory' => $hist23()],
            ['id' => 'ai-003', 'category' => 'AI-COM', 'brand' => 'HP', 'model' => 'ZBook Fury G11 AI (2024)', 'price' => 175000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-14900HX', 'Main Memory' => '64GB DDR5 ECC', 'Storage' => '2TB SSD NVMe', 'Display Screen' => '16" 4K (3840x2160) OLED', 'Graphics' => 'NVIDIA RTX 6000 Ada (48GB)', 'GPU Memory' => '48GB GDDR6', 'CUDA Cores' => '18176', 'OS' => 'Windows 11 Pro Workstation', 'Warranty' => '3 Years Hardware Support'], 'editHistory' => $hist23()],
            ['id' => 'ai-004', 'category' => 'AI-COM', 'brand' => 'Lenovo', 'model' => 'ThinkPad P1 AI Workstation', 'price' => 155000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-14700HX', 'Main Memory' => '32GB DDR5 ECC', 'Storage' => '1TB M.2 NVMe SSD', 'Display Screen' => '16" 4K OLED (3840x2400)', 'Graphics' => 'NVIDIA RTX 5880 Ada (48GB)', 'GPU Memory' => '48GB GDDR6', 'CUDA Cores' => '14080', 'OS' => 'Windows 11 Pro', 'Warranty' => '3 Years Depot Support'], 'editHistory' => $hist23()],
            ['id' => 'ai-005', 'category' => 'AI-COM', 'brand' => 'ASUS', 'model' => 'ProArt Creator 16 (AI Pro)', 'price' => 210000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-14900HX', 'Main Memory' => '96GB DDR5', 'Storage' => '4TB M.2 NVMe SSD', 'Display Screen' => '16" 4K OLED (3840x2400) 120Hz', 'Graphics' => 'NVIDIA RTX 6000 Ada (48GB)', 'GPU Memory' => '48GB GDDR6', 'CUDA Cores' => '18176', 'OS' => 'Windows 11 Pro', 'Warranty' => '3 Years Extended Support'], 'editHistory' => $hist23()],
            ['id' => 'ai-006', 'category' => 'AI-COM', 'brand' => 'Dell', 'model' => 'Precision 7780 (AI Maximum)', 'price' => 300000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Xeon W9-3595X', 'Main Memory' => '192GB DDR5 ECC', 'Storage' => '4TB M.2 NVMe SSD RAID', 'Display Screen' => '17.3" 4K+ OLED (3840x2400)', 'Graphics' => '2x NVIDIA RTX 6000 Ada (48GB each)', 'GPU Memory' => '96GB GDDR6 Total', 'CUDA Cores' => '36352 Total', 'OS' => 'Windows 11 Pro Workstation', 'Warranty' => '5 Years Premium ProSupport'], 'editHistory' => $hist23()],

            // Additional PC products (pc-002 to pc-006)
            ['id' => 'pc-002', 'category' => 'PC', 'brand' => 'HP', 'model' => 'EliteDesk 705 G5 SFF', 'price' => 18900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD Ryzen 5 5600G', 'Main Memory' => '8GB DDR4', 'Storage' => '256GB SSD', 'Graphics' => 'AMD Radeon Graphics', 'Power Supply' => '500W', 'Connectivity' => 'Gigabit Ethernet', 'OS' => 'Windows 10 Pro', 'Weight' => '4.2 kg'], 'editHistory' => $hist23()],
            ['id' => 'pc-003', 'category' => 'PC', 'brand' => 'Dell', 'model' => 'OptiPlex 7090 SFF', 'price' => 26900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-11700', 'Main Memory' => '16GB DDR4', 'Storage' => '512GB SSD + 1TB HDD', 'Graphics' => 'Intel UHD Graphics 730', 'Power Supply' => '725W Gold Rated', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 5', 'OS' => 'Windows 11 Pro', 'Weight' => '5.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'pc-004', 'category' => 'PC', 'brand' => 'Lenovo', 'model' => 'ThinkCentre M90', 'price' => 21900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i5-12400', 'Main Memory' => '8GB DDR4', 'Storage' => '256GB SSD', 'Graphics' => 'Intel UHD Graphics 730', 'Power Supply' => '600W', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 5', 'OS' => 'Windows 11 Home', 'Weight' => '5.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'pc-005', 'category' => 'PC', 'brand' => 'ACER', 'model' => 'Aspire X1930 Bundle', 'price' => 19900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Celeron G6900', 'Main Memory' => '4GB DDR4', 'Storage' => '128GB SSD', 'Graphics' => 'Intel UHD Graphics 730', 'Power Supply' => '400W', 'Connectivity' => 'Gigabit Ethernet', 'OS' => 'Windows 11 Home', 'Weight' => '3.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'pc-006', 'category' => 'PC', 'brand' => 'HP', 'model' => 'Pavilion TG01-2000', 'price' => 32900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-12700', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB SSD + 2TB HDD', 'Graphics' => 'Intel UHD Graphics 770', 'Power Supply' => '1000W Gold Rated', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 6', 'OS' => 'Windows 11 Home', 'Weight' => '8.2 kg'], 'editHistory' => $hist23()],

            // Additional Gaming Desktop products (gpc-002 to gpc-006)
            ['id' => 'gpc-002', 'category' => 'gaming-desktop-pc', 'brand' => 'MSI', 'model' => 'MPG Sekira 500P', 'price' => 42900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-13900K', 'Main Memory' => '64GB DDR5 7200MHz', 'Storage' => '2TB M.2 NVMe SSD + 4TB HDD', 'Graphics' => 'NVIDIA RTX 4090 (24GB GDDR6X)', 'Power Supply' => '1200W 80+ Platinum', 'GPU RAM' => '24GB GDDR6X', 'Monitor Size' => '32" 4K 144Hz Gaming Display', 'Gaming Performance' => '360+ FPS (1440p Ultra)'], 'editHistory' => $hist23()],
            ['id' => 'gpc-003', 'category' => 'gaming-desktop-pc', 'brand' => 'HP', 'model' => 'OMEN 45L', 'price' => 38900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-14700K', 'Main Memory' => '32GB DDR5', 'Storage' => '1TB SSD + 2TB HDD', 'Graphics' => 'NVIDIA RTX 4070 Ti (12GB)', 'Power Supply' => '1000W Gold', 'GPU RAM' => '12GB GDDR6X', 'Monitor Size' => '27" 1440p 240Hz Included', 'Gaming Performance' => '200+ FPS (1440p High)'], 'editHistory' => $hist23()],
            ['id' => 'gpc-004', 'category' => 'gaming-desktop-pc', 'brand' => 'Corsair', 'model' => 'Corsair iCUE 5000T RGB', 'price' => 45900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-14900KS', 'Main Memory' => '64GB DDR5 6000MHz', 'Storage' => '2TB NVMe SSD + 4TB HDD RAID', 'Graphics' => 'NVIDIA RTX 4090 (24GB)', 'Power Supply' => '1500W 80+ Platinum', 'GPU RAM' => '24GB GDDR6X', 'Monitor Size' => '34" Ultrawide 1440p 240Hz', 'Gaming Performance' => '400+ FPS (Optimized)'], 'editHistory' => $hist23()],
            ['id' => 'gpc-005', 'category' => 'gaming-desktop-pc', 'brand' => 'ACER', 'model' => 'Nitro 50 N50-640', 'price' => 32900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-12700', 'Main Memory' => '16GB DDR4', 'Storage' => '512GB SSD + 1TB HDD', 'Graphics' => 'NVIDIA RTX 3060 Ti (8GB)', 'Power Supply' => '750W 80+ Gold', 'GPU RAM' => '8GB GDDR6', 'Monitor Size' => '27" 1440p 144Hz', 'Gaming Performance' => '120+ FPS (1440p High)'], 'editHistory' => $hist23()],
            ['id' => 'gpc-006', 'category' => 'gaming-desktop-pc', 'brand' => 'Lenovo', 'model' => 'Legion Tower 7i (Gen 9)', 'price' => 65000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i9-14900KF', 'Main Memory' => '128GB DDR5 7200MHz', 'Storage' => '4TB M.2 NVMe SSD (Raid 0) + 8TB HDD', 'Graphics' => 'NVIDIA RTX 4090 (24GB GDDR6X)', 'Power Supply' => '1600W 80+ Titanium', 'GPU RAM' => '24GB GDDR6X', 'Monitor Size' => '34" Curved Ultrawide 1440p 240Hz', 'Gaming Performance' => '500+ FPS (Extreme)'], 'editHistory' => $hist23()],

            // Additional Mini PC products (mini-002 to mini-006)
            ['id' => 'mini-002', 'category' => 'mini-pc', 'brand' => 'HP', 'model' => 'EliteDesk 800 G6 Mini', 'price' => 12900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-10700T', 'Main Memory' => '16GB DDR4', 'Storage' => '512GB SSD', 'Graphics' => 'Intel UHD Graphics 630', 'Power Consumption' => '20W', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 5, Bluetooth 5.0', 'OS' => 'Windows 10 Pro / 11 Pro', 'Weight' => '0.9 kg'], 'editHistory' => $hist23()],
            ['id' => 'mini-003', 'category' => 'mini-pc', 'brand' => 'Intel', 'model' => 'NUC 13 Pro (Raptor Lake)', 'price' => 10900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-1365U', 'Main Memory' => '16GB DDR5', 'Storage' => '512GB M.2 SSD', 'Graphics' => 'Intel Iris Xe Graphics', 'Power Consumption' => '25W', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 6E, Bluetooth 5.3', 'OS' => 'Windows 11 Pro', 'Weight' => '0.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'mini-004', 'category' => 'mini-pc', 'brand' => 'Lenovo', 'model' => 'ThinkCentre M75q-2', 'price' => 9900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD Ryzen 7 Pro 5850U', 'Main Memory' => '16GB DDR4', 'Storage' => '512GB M.2 SSD', 'Graphics' => 'AMD Radeon Graphics', 'Power Consumption' => '28W', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 5, Bluetooth 5.1', 'OS' => 'Windows 10 Pro / 11 Pro', 'Weight' => '1.1 kg'], 'editHistory' => $hist23()],
            ['id' => 'mini-005', 'category' => 'mini-pc', 'brand' => 'Dell', 'model' => 'OptiPlex 3000 Micro', 'price' => 6900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Celeron 5305U', 'Main Memory' => '4GB DDR4', 'Storage' => '128GB SSD', 'Graphics' => 'Intel UHD Graphics', 'Power Consumption' => '12W', 'Connectivity' => 'Gigabit Ethernet, Wi-Fi 5, Bluetooth 5.0', 'OS' => 'Windows 10 Pro', 'Weight' => '0.25 kg'], 'editHistory' => $hist23()],
            ['id' => 'mini-006', 'category' => 'mini-pc', 'brand' => 'ASUS', 'model' => 'ProArt PA148CTC Portable', 'price' => 15900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Core i7-1195G7', 'Main Memory' => '32GB DDR4', 'Storage' => '1TB M.2 SSD', 'Graphics' => 'Intel Iris Xe Graphics', 'Power Consumption' => '30W', 'Connectivity' => 'Thunderbolt 4, Wi-Fi 6E, Bluetooth 5.2', 'OS' => 'Windows 11 Pro', 'Weight' => '0.8 kg'], 'editHistory' => $hist23()],

            // Additional Server products (svr-002 to svr-006)
            ['id' => 'svr-002', 'category' => 'Server', 'brand' => 'Dell', 'model' => 'PowerEdge R7525', 'price' => 275000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD EPYC 7742 (2 sockets, 128 cores)', 'Main Memory' => '512GB DDR4 RDIMM', 'Storage' => '16x 960GB SSD SAS (RAID 10)', 'RAID Controller' => 'PERC H740P Adapter', 'Network' => '4x 10Gb Ethernet Ports', 'Power Supply' => '2x 2000W Platinum', 'Form Factor' => '2U Rack Mount', 'Warranty' => '3 Years ProSupport Enterprise'], 'editHistory' => $hist23()],
            ['id' => 'svr-003', 'category' => 'Server', 'brand' => 'Lenovo', 'model' => 'ThinkSystem SR860 V3', 'price' => 320000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Xeon Platinum 8490H (2 sockets)', 'Main Memory' => '512GB DDR5 RDIMM', 'Storage' => '24x 1.92TB NVMe SSD (RAID 60)', 'RAID Controller' => 'Lenovo ThinkSystem RAID 9450-16i', 'Network' => '2x 100Gb Ethernet Adapters', 'Power Supply' => '2x 2000W Platinum', 'Form Factor' => '2U Rack Server', 'Warranty' => '5 Years On-site'], 'editHistory' => $hist23()],
            ['id' => 'svr-004', 'category' => 'Server', 'brand' => 'HPE', 'model' => 'ProLiant DL160 Gen11', 'price' => 250000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Xeon Gold 6548 (1 socket)', 'Main Memory' => '256GB DDR5 RDIMM', 'Storage' => '8x 960GB SAS SSD (RAID 5)', 'RAID Controller' => 'HPE Smart Array E208i-a SR Gen11', 'Network' => '2x 10Gb Ethernet', 'Power Supply' => '1x 1600W Platinum', 'Form Factor' => '1U Rack Server', 'Warranty' => '3 Years Hardware Support'], 'editHistory' => $hist23()],
            ['id' => 'svr-005', 'category' => 'Server', 'brand' => 'Dell', 'model' => 'PowerEdge R6715', 'price' => 180000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'AMD EPYC 9334 (1 socket, 12 cores)', 'Main Memory' => '128GB DDR5 RDIMM', 'Storage' => '4x 960GB SSD SAS (RAID 5)', 'RAID Controller' => 'PERC H745P Adapter', 'Network' => '2x 10Gb Ethernet Ports', 'Power Supply' => '1x 1400W Platinum', 'Form Factor' => '1U Rack Mount', 'Warranty' => '3 Years Limited Hardware'], 'editHistory' => $hist23()],
            ['id' => 'svr-006', 'category' => 'Server', 'brand' => 'Lenovo', 'model' => 'ThinkSystem SR630 V4', 'price' => 400000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Processor' => 'Intel Xeon Platinum 8592+ (2 sockets, 64 cores)', 'Main Memory' => '768GB DDR5 RDIMM', 'Storage' => '32x 3.84TB NVMe SSD (RAID 60)', 'RAID Controller' => 'Lenovo ThinkSystem RAID 940-16i-2GB', 'Network' => '4x 100Gb Ethernet Adapters', 'Power Supply' => '2x 2000W Platinum', 'Form Factor' => '4U Rack Server', 'Warranty' => '5 Years Premium Support'], 'editHistory' => $hist23()],

            // Monitor products (mon-001 to mon-005)
            ['id' => 'mon-001', 'category' => 'monitor', 'brand' => 'Dell', 'model' => 'UltraSharp U2724DE', 'price' => 18900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Display Size' => '27" (68.6 cm)', 'Resolution' => '2560 x 1440 (QHD) 16:9', 'Panel Type' => 'IPS', 'Color Gamut' => 'sRGB 99%, DCI-P3 90%', 'Brightness' => '350 nits', 'Contrast Ratio' => '1000:1', 'Response Time' => '8ms (typical)', 'Ports' => 'USB-C with 90W Power Delivery, HDMI, DisplayPort', 'Stand' => 'Height Adjustable, Pivot, Swivel, Tilt', 'Weight' => '3.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'mon-002', 'category' => 'monitor', 'brand' => 'LG', 'model' => 'UltraWide 34UP550', 'price' => 28900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Display Size' => '34" (86.7 cm)', 'Resolution' => '3440 x 1440 (Ultrawide) 21:9', 'Panel Type' => 'Nano Cell IPS', 'Color Gamut' => 'DCI-P3 98.5%', 'Brightness' => '400 nits', 'Contrast Ratio' => '1000:1', 'Response Time' => '5ms', 'Ports' => 'Thunderbolt 3, HDMI, DisplayPort, USB-C', 'Stand' => 'Ergonomic Height/Tilt', 'Weight' => '5.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'mon-003', 'category' => 'monitor', 'brand' => 'ASUS', 'model' => 'ProArt PA248QV', 'price' => 12900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Display Size' => '24.1" (61.1 cm)', 'Resolution' => '1920 x 1200 (WUXGA)', 'Panel Type' => 'IPS', 'Color Gamut' => 'sRGB 99.8%', 'Brightness' => '300 nits', 'Contrast Ratio' => '1000:1', 'Response Time' => '5ms', 'Ports' => 'HDMI, DisplayPort, USB-C', 'Calibration' => 'Factory Calibrated', 'Weight' => '2.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'mon-004', 'category' => 'monitor', 'brand' => 'BenQ', 'model' => 'SW240', 'price' => 15900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Display Size' => '24" (61 cm)', 'Resolution' => '1920 x 1200 (WUXGA)', 'Panel Type' => 'IPS', 'Color Gamut' => 'Adobe RGB 99%', 'Brightness' => '270 nits', 'Contrast Ratio' => '1000:1', 'Response Time' => '5ms', 'Ports' => 'HDMI, DisplayPort, DVI-D, USB 3.0', 'Calibration' => 'Calibrated', 'Weight' => '3.1 kg'], 'editHistory' => $hist23()],
            ['id' => 'mon-005', 'category' => 'monitor', 'brand' => 'HP', 'model' => 'Z32 4K', 'price' => 35900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Display Size' => '32" (81.3 cm)', 'Resolution' => '3840 x 2160 (4K) UHD', 'Panel Type' => 'IPS', 'Color Gamut' => 'DCI-P3 97.8%', 'Brightness' => '400 nits', 'Contrast Ratio' => '1000:1', 'Response Time' => '8ms', 'Ports' => 'HDMI, DisplayPort, USB-C (90W)', 'Stand' => 'Fully Adjustable', 'Weight' => '8.2 kg'], 'editHistory' => $hist23()],

            // UPS products (ups-001 to ups-005)
            ['id' => 'ups-001', 'category' => 'ups', 'brand' => 'APC', 'model' => 'Smart-UPS SMT750', 'price' => 8900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Power Rating' => '750VA / 500W', 'Battery Type' => 'Sealed Lead Acid', 'Backup Time' => '5-8 minutes (50% load)', 'Outlets' => '6 Total (4 Battery, 2 Surge)', 'Runtime' => '8 Outlet Positions', 'Input Voltage' => '120V', 'Output Voltage' => '120V', 'Communication Ports' => 'RJ-45 Network Jack, USB', 'Dimensions' => '430 x 248 x 175 mm', 'Weight' => '12.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'ups-002', 'category' => 'ups', 'brand' => 'CyberPower', 'model' => 'CP1500AVRLCD', 'price' => 6900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Power Rating' => '1500VA / 900W', 'Battery Type' => 'Sealed Lead Acid', 'Backup Time' => '10-15 minutes (50% load)', 'Outlets' => '12 Total (8 Battery, 4 Surge)', 'LCD Display' => 'Yes', 'Input Voltage' => '120V', 'Output Voltage' => '120V', 'Communication Ports' => 'RJ-45, USB, Serial', 'Runtime' => 'Extended Time', 'Weight' => '15.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'ups-003', 'category' => 'ups', 'brand' => 'Eaton', 'model' => '5E 1500', 'price' => 7900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Power Rating' => '1500VA / 900W', 'Battery Type' => 'Lead-Acid Battery', 'Backup Time' => '12 minutes (Full Load)', 'Outlets' => '8 Total', 'LCD Display' => 'Yes with Menu', 'Input Voltage' => '230V', 'Output Voltage' => '230V', 'Surge Protection' => 'Full Power Conditioning', 'Dimensions' => '442 x 201 x 127 mm', 'Weight' => '14.2 kg'], 'editHistory' => $hist23()],
            ['id' => 'ups-004', 'category' => 'ups', 'brand' => 'Vertiv', 'model' => 'Liebert GXT-MT+', 'price' => 22900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Power Rating' => '2000VA / 1400W', 'Battery Type' => 'Hot-Swappable Lead-Acid', 'Backup Time' => '15-20 minutes (50% load)', 'Outlets' => '8 Outlets', 'Efficiency' => '98% (ECO Mode)', 'Input Voltage' => '230V', 'Output Voltage' => '230V', 'Communication' => 'USB, Ethernet SNMP', 'Runtime Expansion' => 'Possible', 'Weight' => '18.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'ups-005', 'category' => 'ups', 'brand' => 'APC', 'model' => 'Smart-UPS X 3000VA', 'price' => 35900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Power Rating' => '3000VA / 2250W', 'Battery Type' => 'Hot-Swappable Lead-Acid', 'Backup Time' => '25 minutes (50% load)', 'Outlets' => '10 Controllable Outlets', 'Efficiency' => '98%', 'Input/Output' => '230V', 'Management' => 'Network Card, USB, Modbus Support', 'Expandability' => '3x Battery Modules', 'Dimensions' => '435 x 630 x 178 mm', 'Weight' => '26.5 kg'], 'editHistory' => $hist23()],

            // Software products (sof-001 to sof-005)
            ['id' => 'sof-001', 'category' => 'software', 'brand' => 'Microsoft', 'model' => 'Windows 11 Pro License', 'price' => 5900, 'priceUnit' => 'บาท/ใบ', 'priceDate' => '2569-05-23', 'specs' => ['Product Type' => 'Operating System', 'Version' => 'Windows 11 Pro', 'License Type' => 'Volume License', 'Installation' => 'Digital Download', 'Support Duration' => '5 Years', 'Updates' => 'Free Updates & Security Patches', 'Compatibility' => 'Intel/AMD', 'Minimum RAM' => '4GB', 'Minimum Storage' => '64GB SSD'], 'editHistory' => $hist23()],
            ['id' => 'sof-002', 'category' => 'software', 'brand' => 'Autodesk', 'model' => 'AutoCAD 2024', 'price' => 45000, 'priceUnit' => 'บาท/ปี', 'priceDate' => '2569-05-23', 'specs' => ['Product Type' => 'CAD Software', 'Version' => 'AutoCAD 2024', 'License Duration' => '1 Year Subscription', 'Platform' => 'Windows/Mac', 'Cloud Features' => 'Autodesk Cloud Support', 'Support Included' => 'Technical Support', 'File Format' => 'DWG, DXF, PDF', 'Updates' => 'Regular Updates Included'], 'editHistory' => $hist23()],
            ['id' => 'sof-003', 'category' => 'software', 'brand' => 'Adobe', 'model' => 'Creative Cloud Suite', 'price' => 65900, 'priceUnit' => 'บาท/ปี', 'priceDate' => '2569-05-23', 'specs' => ['Product Type' => 'Design Software Suite', 'Version' => 'Creative Cloud 2024', 'License Duration' => '1 Year Subscription', 'Applications' => 'Photoshop, Illustrator, InDesign, Premiere Pro + 20 more', 'Cloud Storage' => '100GB', 'Platform' => 'Windows/Mac', 'Updates' => 'Continuous Updates', 'Support' => '24/7 Community Support'], 'editHistory' => $hist23()],
            ['id' => 'sof-004', 'category' => 'software', 'brand' => 'JetBrains', 'model' => 'IntelliJ IDEA Ultimate', 'price' => 14900, 'priceUnit' => 'บาท/ปี', 'priceDate' => '2569-05-23', 'specs' => ['Product Type' => 'IDE Software', 'Version' => '2024.1', 'License Duration' => '1 Year', 'Supported Languages' => 'Java, Kotlin, Scala, Groovy + 10 more', 'Platform' => 'Windows/Mac/Linux', 'Plugins' => '1000+ Third-party Plugins', 'Cloud Support' => 'JetBrains Cloud', 'Upgrade' => 'Free Major Upgrades'], 'editHistory' => $hist23()],
            ['id' => 'sof-005', 'category' => 'software', 'brand' => 'Jetbrains', 'model' => 'VS Code Extensions Bundle', 'price' => 1900, 'priceUnit' => 'บาท/ปี', 'priceDate' => '2569-05-23', 'specs' => ['Product Type' => 'Code Editor Extensions', 'Base Application' => 'Visual Studio Code', 'License Type' => 'Subscription', 'Included Extensions' => '100+ Professional Extensions', 'Support Duration' => '1 Year', 'Updates' => 'Weekly Updates', 'Installation' => 'One-click Installation', 'Platforms' => 'Windows/Mac/Linux'], 'editHistory' => $hist23()],

            // Projector products (prj-001 to prj-005)
            ['id' => 'prj-001', 'category' => 'projector', 'brand' => 'Epson', 'model' => 'EB-2250U', 'price' => 89900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Brightness' => '5000 Lumens (Color & White)', 'Resolution' => 'WUXGA (1920 x 1200)', 'Contrast Ratio' => '15000:1', 'Projection Distance' => '1.52-24.32m', 'Zoom Ratio' => '1.6x', 'Lens Type' => 'Fixed with Manual Lens Shift', 'Light Source' => '300W UHP Lamp', 'Lamp Life' => '5000 hours (Normal)', 'Connectivity' => 'HDMI, VGA, Composite, Ethernet'], 'editHistory' => $hist23()],
            ['id' => 'prj-002', 'category' => 'projector', 'brand' => 'Sony', 'model' => 'VPL-PHZ10', 'price' => 125000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Brightness' => '5200 Lumens', 'Resolution' => 'WUXGA (1920 x 1200)', 'Contrast Ratio' => '20000:1', 'Projection Technology' => '3LCD', 'Lamp Type' => 'UHP 300W', 'Lamp Life' => '6000 hours', 'Lens Shift' => 'Vertical & Horizontal', 'Network Features' => 'Built-in Network Interface', 'Weight' => '5.4 kg'], 'editHistory' => $hist23()],
            ['id' => 'prj-003', 'category' => 'projector', 'brand' => 'Panasonic', 'model' => 'PT-RZ970', 'price' => 95000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Brightness' => '6500 Lumens', 'Resolution' => 'WUXGA (1920 x 1200)', 'Contrast Ratio' => '3000:1', 'Light Source' => 'DLP Technology', 'Lens Shift' => 'Full Range Auto Lens Shift', 'Zoom' => '1.8x Optical Zoom', 'Connectivity' => 'HDMI x2, VGA, 3G-SDI', 'Cooling' => 'High-Speed Cooling System', 'Weight' => '4.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'prj-004', 'category' => 'projector', 'brand' => 'Canon', 'model' => 'REALiS WUX4000', 'price' => 185000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Brightness' => '4000 Lumens (Color & White)', 'Resolution' => '4K (4096 x 2400)', 'Contrast Ratio' => '20000:1', 'Light Source' => 'Mercury Lamp 300W', 'Projection Technology' => 'LCOS', 'Zoom Ratio' => '2.0x', 'Lens Shift' => 'Wide Horizontal/Vertical', 'Connectivity' => 'HDBaseT, HDMI, Composite', 'Weight' => '7.2 kg'], 'editHistory' => $hist23()],
            ['id' => 'prj-005', 'category' => 'projector', 'brand' => 'BenQ', 'model' => 'MH535A', 'price' => 32900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Brightness' => '3600 Lumens', 'Resolution' => 'Full HD (1920 x 1080)', 'Contrast Ratio' => '10000:1', 'Projection Technology' => 'DLP', 'Lamp Type' => 'UHP 210W', 'Lamp Life' => '5000 hours', 'Zoom' => '1.2x Fixed', 'Connectivity' => 'HDMI, VGA, USB, Ethernet', 'Weight' => '2.5 kg'], 'editHistory' => $hist23()],

            // Printer products (prt-001 to prt-005)
            ['id' => 'prt-001', 'category' => 'printer', 'brand' => 'HP', 'model' => 'LaserJet Pro M404n', 'price' => 8900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Printer Type' => 'Monochrome Laser Printer', 'Print Speed' => '40 ppm', 'Resolution' => '600 x 600 dpi', 'Monthly Volume' => 'Up to 80,000 pages', 'Input Tray' => '350 Sheets', 'Output Tray' => '100 Sheets', 'Connectivity' => 'Ethernet, USB 2.0', 'Toner Cartridge' => 'Standard (3,100 pages)', 'Weight' => '15.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'prt-002', 'category' => 'printer', 'brand' => 'Canon', 'model' => 'imagePROGRAF TM-200', 'price' => 45000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Printer Type' => 'Technical CAD Printer', 'Print Width' => '609mm (24")', 'Print Speed' => '15.5 seconds (Color)', 'Resolution' => '2400 x 1200 dpi', 'Ink Type' => '5-Color Pigment Ink', 'Paper Capacity' => 'Roll Feed', 'Connectivity' => 'Ethernet, USB', 'Memory' => '512MB', 'Weight' => '28 kg'], 'editHistory' => $hist23()],
            ['id' => 'prt-003', 'category' => 'printer', 'brand' => 'Xerox', 'model' => 'VersaLink C9070', 'price' => 285000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Printer Type' => 'Color Multifunction Printer', 'Print Speed' => '70 ppm', 'Resolution' => '1200 x 2400 dpi', 'Monthly Volume' => 'Up to 500,000 pages', 'Copy/Scan' => 'Yes', 'Fax' => 'Yes', 'Connectivity' => 'Ethernet, Wireless', 'Paper Capacity' => '1820 sheets', 'Weight' => '125 kg'], 'editHistory' => $hist23()],
            ['id' => 'prt-004', 'category' => 'printer', 'brand' => 'Brother', 'model' => 'HL-L9310CDW', 'price' => 12900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Printer Type' => 'Color Laser Printer', 'Print Speed' => '33 ppm (Color & B/W)', 'Resolution' => '2400 x 600 dpi', 'Monthly Volume' => 'Up to 50,000 pages', 'Connectivity' => 'USB, Ethernet, Wi-Fi', 'Paper Capacity' => '250 Sheets', 'Memory' => '1GB', 'Toner Type' => 'TN348', 'Weight' => '15 kg'], 'editHistory' => $hist23()],
            ['id' => 'prt-005', 'category' => 'printer', 'brand' => 'Epson', 'model' => 'WorkForce Pro WF-C5890', 'price' => 28900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Printer Type' => 'Color Inkjet MFP', 'Print Speed' => '25 ppm', 'Resolution' => '5760 x 1440 dpi', 'Monthly Volume' => 'Up to 60,000 pages', 'Functions' => 'Print/Copy/Scan/Fax', 'Ink Type' => 'Heat-Free Pigment Ink', 'Connectivity' => 'Ethernet, USB, Wi-Fi', 'Paper Capacity' => '580 sheets', 'Weight' => '16.5 kg'], 'editHistory' => $hist23()],

            // Network products (net-001 to net-005)
            ['id' => 'net-001', 'category' => 'network', 'brand' => 'Cisco', 'model' => 'Catalyst 2960X-48LPS', 'price' => 65000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Type' => 'Managed Layer 2 Switch', 'Ports' => '48 x 1G PoE+ Ports + 4 x 10G Uplink', 'Forwarding Rate' => '176 Gbps', 'Memory' => '4GB DRAM, 128MB Flash', 'Power Consumption' => '381W Max', 'PoE Budget' => '740W', 'Management' => 'Web UI, SSH, Telnet', 'Dimensions' => '435 x 483 x 44mm', 'Weight' => '7.2 kg'], 'editHistory' => $hist23()],
            ['id' => 'net-002', 'category' => 'network', 'brand' => 'Juniper', 'model' => 'SRX340', 'price' => 125000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Type' => 'Security Gateway', 'Throughput' => '5 Gbps', 'Ports' => '4 x Gigabit Ethernet', 'IPsec Throughput' => '500 Mbps', 'Firewall Rules' => 'Up to 100,000', 'Memory' => '4GB DRAM', 'Management' => 'J-Web, SSH, CLI', 'Power Supply' => 'Dual Redundant', 'Weight' => '5.8 kg'], 'editHistory' => $hist23()],
            ['id' => 'net-003', 'category' => 'network', 'brand' => 'Arista', 'model' => '7050SX3-48YC8', 'price' => 185000, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Type' => '25/100G Cloud Switch', 'Ports' => '48 x 25GbE + 8 x 100GbE', 'Bandwidth' => '14.4 Tbps', 'Latency' => '<150ns', 'Memory' => '32GB DDR4', 'Management' => 'eAPI, SSH, CloudVision', 'Power Consumption' => '2.2kW', 'Operating Temp' => '0-45°C', 'Weight' => '18 kg'], 'editHistory' => $hist23()],
            ['id' => 'net-004', 'category' => 'network', 'brand' => 'TP-Link', 'model' => 'TL-SG3428X', 'price' => 18900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Type' => 'Managed Gigabit Switch', 'Ports' => '24 x 1Gbps + 4 x 10Gbps SFP+', 'Bandwidth' => '240 Gbps', 'Packet Buffer' => '12 Mbits', 'PoE Support' => 'No', 'Management' => 'Web UI, Telnet, SSH', 'Power Input' => '180W', 'Operating Temp' => '0-45°C', 'Weight' => '4.5 kg'], 'editHistory' => $hist23()],
            ['id' => 'net-005', 'category' => 'network', 'brand' => 'Ubiquiti', 'model' => 'EdgeRouter 12P', 'price' => 28900, 'priceUnit' => 'บาท/เครื่อง', 'priceDate' => '2569-05-23', 'specs' => ['Type' => 'Routing & Switching', 'Ports' => '10 x Gigabit Ethernet + 2 x SFP', 'Throughput' => '3.4 Gbps', 'Memory' => '2GB DDR3, 2GB NAND Flash', 'Management' => 'SSH, Web UI, SNMP', 'Power Consumption' => '60W Max', 'Dimensions' => '245 x 160 x 45mm', 'Temperature Range' => '0-40°C', 'Weight' => '0.9 kg'], 'editHistory' => $hist23()],
        ];
    }
}
