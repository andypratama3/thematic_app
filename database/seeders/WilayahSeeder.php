<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Fetching provinces from API...');
        $this->seedProvinces();

        $this->command->info('Fetching regencies from API...');
        $this->seedRegencies();

        $this->command->info('Fetching districts from API...');
        $this->seedDistricts();

        $this->command->info('Fetching villages from API...');
        $this->seedVillages();

        $this->command->info('Wilayah data seeded successfully!');
    }

    private function seedProvinces()
    {
        $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');

        if ($response->successful()) {
            $provinces = $response->json();

            foreach ($provinces as $province) {
                DB::table('provinces')->updateOrInsert(
                    ['code' => $province['id']],
                    [
                        'name' => $province['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedRegencies()
    {
        $provinces = DB::table('provinces')->get();

        foreach ($provinces as $province) {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$province->code}.json");

            if ($response->successful()) {
                $regencies = $response->json();

                foreach ($regencies as $regency) {
                    DB::table('regencies')->updateOrInsert(
                        ['code' => $regency['id']],
                        [
                            'province_id' => $province->id,
                            'name' => $regency['name'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            usleep(100000); // Rate limiting
        }
    }

    private function seedDistricts()
    {
        // Only seed for specific regencies to avoid too much data
        $targetRegencies = DB::table('regencies')
            ->where('name', 'like', '%KEDIRI%')
            ->orWhere('name', 'like', '%SURABAYA%')
            ->get();

        foreach ($targetRegencies as $regency) {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regency->code}.json");

            if ($response->successful()) {
                $districts = $response->json();

                foreach ($districts as $district) {
                    DB::table('districts')->updateOrInsert(
                        ['code' => $district['id']],
                        [
                            'regency_id' => $regency->id,
                            'name' => $district['name'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            usleep(100000);
        }
    }

    private function seedVillages()
    {
        // Only seed villages for specific districts
        $targetDistricts = DB::table('districts')
            ->whereIn('regency_id', function($query) {
                $query->select('id')
                    ->from('regencies')
                    ->where('name', 'like', '%KEDIRI%');
            })
            ->limit(5)
            ->get();

        foreach ($targetDistricts as $district) {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$district->code}.json");

            if ($response->successful()) {
                $villages = $response->json();

                foreach ($villages as $village) {
                    DB::table('villages')->updateOrInsert(
                        ['code' => $village['id']],
                        [
                            'district_id' => $district->id,
                            'name' => $village['name'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            usleep(100000);
        }
    }
}
