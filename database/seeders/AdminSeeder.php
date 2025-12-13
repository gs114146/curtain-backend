<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@curtainshop.com';
        User::updateOrCreate(['email' => $email], [
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make('123456789')
        ]);
    }
}
