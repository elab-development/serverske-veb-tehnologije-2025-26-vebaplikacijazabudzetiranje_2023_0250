<?php

//migracija koja dodaje kolonu "role" u tabelu "users"

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() = šta se desi kad primeniš migraciju (dodaj kolonu)
     */
    public function up(): void
    {
        // Schema::table (ne "create"!) znači da MENJAMO postojeću tabelu
        Schema::table('users', function (Blueprint $table) {
            // enum kolona - sme da ima samo jednu od navedenih vrednosti
            // ->default('user') - ako se ne navede, automatski postaje "user"
            // ->after('email') - postavlja kolonu odmah posle "email" kolone (samo estetski, radi lakšeg čitanja u bazi)
            $table->enum('role', ['admin', 'authenticated_user', 'user'])
                  ->default('user')
                  ->after('email');
        });
    }

    /**
     * down() = šta se desi kad PONIŠTIŠ migraciju (ukloni kolonu)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};