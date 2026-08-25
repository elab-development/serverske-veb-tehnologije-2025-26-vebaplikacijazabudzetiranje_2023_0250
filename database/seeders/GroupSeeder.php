<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Group::factory()->count(10)->create();
    }
}