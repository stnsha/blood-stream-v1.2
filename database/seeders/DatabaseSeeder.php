<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Lab;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::create([
            'name' => 'superadmin',
            'email' => 'anasuharosli.alphac@gmail.com',
            'password' => Hash::make('anasuha97')
        ]);

        $name = 'Innoquest Pathology Sdn Bhd';
        Lab::create([
            'name' => $name,
            'path' => generate_lab_path($name),
            'code' =>  generate_lab_code($name),
            'status' => 1,
        ]);
    }
}
