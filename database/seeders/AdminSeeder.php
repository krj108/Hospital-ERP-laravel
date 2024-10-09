<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\App\Models\User; 
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create the admin user
        $admin = User::create([
            'name' => 'Admin User 1',
            'email' => 'admin1@example.com',
            'password' => bcrypt('password'),
        ]);

        // Check if the admin role exists, and if not, create it
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Assign the admin role to the user
        $admin->assignRole($adminRole);
    }
}
