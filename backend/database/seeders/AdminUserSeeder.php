<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@helpdesk.com'
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password')
            ]
        );

        $adminRole = Role::where('name', 'ADMIN')->first();

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([
                $adminRole->id
            ]);
        }
    }
}
