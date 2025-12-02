<?php

namespace Database\Seeders;

use App\Models\Scooter;
use Illuminate\Database\Seeder;

class ScooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scooters = [
            [
                'code' => 'SCO-001',
                'qr_code' => 'QR-SCO-001',
                'status' => 'available',
                'battery_percentage' => 85,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'last_seen_at' => now(),
                'is_locked' => true,
                'is_active' => true,
                'device_imei' => 'ESP32_IMEI_001',
                'firmware_version' => '1.0.0',
                'last_maintenance_at' => now()->subDays(5),
            ],
            [
                'code' => 'SCO-002',
                'qr_code' => 'QR-SCO-002',
                'status' => 'available',
                'battery_percentage' => 92,
                'latitude' => 30.0500,
                'longitude' => 31.2400,
                'last_seen_at' => now()->subMinutes(10),
                'is_locked' => true,
                'is_active' => true,
                'device_imei' => 'ESP32_IMEI_002',
                'firmware_version' => '1.0.0',
                'last_maintenance_at' => now()->subDays(3),
            ],
            [
                'code' => 'SCO-003',
                'qr_code' => 'QR-SCO-003',
                'status' => 'charging',
                'battery_percentage' => 45,
                'latitude' => 30.0380,
                'longitude' => 31.2300,
                'last_seen_at' => now()->subMinutes(5),
                'is_locked' => true,
                'is_active' => true,
                'device_imei' => 'ESP32_IMEI_003',
                'firmware_version' => '1.0.0',
                'last_maintenance_at' => now()->subDays(7),
            ],
            [
                'code' => 'SCO-004',
                'qr_code' => 'QR-SCO-004',
                'status' => 'available',
                'battery_percentage' => 78,
                'latitude' => 30.0550,
                'longitude' => 31.2450,
                'last_seen_at' => now()->subMinutes(2),
                'is_locked' => false,
                'is_active' => true,
                'device_imei' => 'ESP32_IMEI_004',
                'firmware_version' => '1.0.0',
                'last_maintenance_at' => now()->subDays(10),
            ],
        ];

        foreach ($scooters as $scooterData) {
            Scooter::firstOrCreate(
                ['code' => $scooterData['code']],
                $scooterData
            );
        }

        $this->command->info('تم إنشاء 4 سكوترات تجريبية بنجاح!');
        $this->command->info('SCO-001, SCO-002, SCO-003, SCO-004');
    }
}
