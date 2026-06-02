<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Dev user — bypasses all permission checks via Gate::before
        User::forceCreate([
            'name'       => 'Dev',
            'email'      => 'dev@dev.com',
            'password'   => Hash::make('dev12345'),
            'cpf'        => '00000000000',
            'is_active'  => true,
            'is_dev'     => true,
        ]);

        // Default admin user
        $admin = User::forceCreate([
            'name'       => 'Administrador',
            'email'      => 'admin@admin.com',
            'password'   => Hash::make('admin12345'),
            'cpf'        => '00000000001',
            'is_active'  => true,
            'is_dev'     => false,
        ]);

        $admin->assignRole('Administrador');
    }
}
