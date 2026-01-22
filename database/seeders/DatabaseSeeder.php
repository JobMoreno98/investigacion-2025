<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = Admin::create([
            'name' => 'Test User',
            'email' => 'jobmoreno.mtz@gmail.com',
            'password' => Hash::make('password')
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'jobmoreno.mtz@gmail.com',
            'password' => Hash::make('password')
        ]);

        Role::create([
            'name' => 'super_admin',
            'guard_name' => 'admin'
        ]);
        $user->assignRole('super_admin');

        $this->call(GeneralDataSeeder::class);
        $this->call(DataSeeder::class);
        $this->call(ProgramasAcademicosSeeder::class);
        $this->call(DivisionesDepartamentosSeeder::class);
    }
}
