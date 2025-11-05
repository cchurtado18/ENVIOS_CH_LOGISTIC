<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $admin = User::where('email', 'admin@chlogistic.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@chlogistic.com',
                'password' => Hash::make('Premium19$$'),
                'role' => 'admin',
                'phone' => '00000000',
                'department' => 'Administración',
                'address' => 'Managua, Nicaragua',
            ]);

            $this->command->info('Usuario administrador creado exitosamente!');
            $this->command->info('Email: admin@chlogistic.com');
        } else {
            $this->command->info('El usuario administrador ya existe.');
        }
    }
}
