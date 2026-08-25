<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Po jedan fiksni nalog za svaku ulogu - radi lakseg testiranja kroz Postman
        User::factory()->create([
            'name' => 'Admin Korisnik',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Autentifikovani Korisnik',
            'email' => 'authenticated@example.com',
            'password' => Hash::make('password'),
            'role' => 'authenticated_user',
        ]);

        User::factory()->create([
            'name' => 'Obican Korisnik',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Dodatni nasumicni korisnici sa podrazumevanom ulogom "user"
        User::factory()->count(7)->create();
    }
}
