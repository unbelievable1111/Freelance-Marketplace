<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Seed the application's database. **/
    public function run(): void
    {
        $this->call(UserRoleSeeder::class);
        
        User::create([
            'name' => 'bob_worker',
            'email' => 'bob_worker@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'alex_customer',
            'email' => 'alex_customer@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 2,
        ]);

        User::create([
            'name' => 'alice_admin',
            'email' => 'alice_admin@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 3,
        ]);

        User::create([
            'name' => 'alex',
            'email' => 'alex@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        $this->call(UserAvatarSeeder::class);
    }
}