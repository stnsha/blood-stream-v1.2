<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Lab;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $name = 'Innoquest Pathology Sdn Bhd';
        Lab::create([
            'name' => $name,
            'path' => generate_lab_path($name),
            'code' =>  generate_lab_code($name),
            'status' => 1,
        ]);
    }
}
