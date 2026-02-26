<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $superadminRole = Role::query()->where('name', 'super_admin')->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $userRole = Role::query()->where('name', 'user')->firstOrFail();

        User::query()->updateOrCreate(['email' => 'superadmin@vitaroot.local'], [
            'name' => 'Super Admin',
            'email' => 'superadmin@vitaroot.local',
            'password' => Hash::make('password123'),
            'role_id' => $superadminRole->id,
        ]);

        User::query()->updateOrCreate(['email' => 'admin@vitaroot.local'], [
            'name' => 'Admin',
            'email' => 'admin@vitaroot.local',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
        ]);

        User::query()->updateOrCreate(['email' => 'user@vitaroot.local'], [
            'name' => 'User',
            'email' => 'user@vitaroot.local',
            'password' => Hash::make('password123'),
            'role_id' => $userRole->id,
        ]);
    }
}
