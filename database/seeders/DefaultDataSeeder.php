<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        // Map Layers
        DB::table('map_layers')->insert([
            [
                'name' => 'Boundary Layer',
                'slug' => 'boundary-layer',
                'description' => 'Administrative boundary layer',
                'type' => 'boundary',
                'min_zoom' => 0,
                'max_zoom' => 22,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Farmer Points',
                'slug' => 'farmer-points',
                'description' => 'Farmer location points',
                'type' => 'point',
                'min_zoom' => 10,
                'max_zoom' => 22,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transaction Points',
                'slug' => 'transaction-points',
                'description' => 'Fertilizer transaction points',
                'type' => 'point',
                'min_zoom' => 10,
                'max_zoom' => 22,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Thematic Maps
        DB::table('thematic_maps')->insert([
            [
                'name' => 'Produksi Tanaman Pangan',
                'slug' => 'produksi-tanaman-pangan',
                'description' => 'Data produksi tanaman pangan per wilayah',
                'category' => 'Dipertabun',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Subsidi Pupuk',
                'slug' => 'subsidi-pupuk',
                'description' => 'Data subsidi pupuk per periode',
                'category' => 'Subsidi',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Settings
        DB::table('settings')->insert([
            ['key' => 'map_default_center_lat', 'value' => '-7.2575', 'type' => 'string', 'group' => 'map', 'description' => 'Default map center latitude', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'map_default_center_lng', 'value' => '112.7521', 'type' => 'string', 'group' => 'map', 'description' => 'Default map center longitude', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'map_default_zoom', 'value' => '10', 'type' => 'integer', 'group' => 'map', 'description' => 'Default map zoom level', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'map_min_zoom', 'value' => '5', 'type' => 'integer', 'group' => 'map', 'description' => 'Minimum map zoom level', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'map_max_zoom', 'value' => '22', 'type' => 'integer', 'group' => 'map', 'description' => 'Maximum map zoom level', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'import_max_file_size', 'value' => '10240', 'type' => 'integer', 'group' => 'import', 'description' => 'Maximum import file size in KB', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'import_allowed_extensions', 'value' => '["xlsx","xls","csv"]', 'type' => 'json', 'group' => 'import', 'description' => 'Allowed file extensions for import', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Default data seeded successfully!');
    }
}
