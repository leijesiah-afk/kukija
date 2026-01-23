<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::create([
            'username' => 'admin',
            'email' => 'admin@kukija.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        AdminUser::create([
            'username' => 'manager',
            'email' => 'manager@kukija.com',
            'password' => Hash::make('manager123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
