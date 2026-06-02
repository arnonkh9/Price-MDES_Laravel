<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'admin',  'password' => 'admin123', 'name' => 'ผู้ดูแลระบบ',         'role' => 'admin', 'department' => 'ฝ่ายไอที'],
            ['username' => 'user01', 'password' => 'user123',  'name' => 'นายสมชาย ใจดี',        'role' => 'user',  'department' => 'ฝ่ายพัสดุ'],
            ['username' => 'user02', 'password' => 'user123',  'name' => 'นางสาวสมหญิง มีสุข',   'role' => 'user',  'department' => 'ฝ่ายจัดซื้อ'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'department' => $u['department'],
                    'password' => Hash::make($u['password']),
                ]
            );
        }
    }
}
