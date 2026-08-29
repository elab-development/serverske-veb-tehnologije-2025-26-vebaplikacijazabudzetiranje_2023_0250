<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dodaje kolonu "paid_at" - stvarni datum kada je trosak placen.
     * Razlikuje se od "created_at" (kada je red unet u aplikaciju).
     * Nullable je da postojeci redovi ne puknu; u kontroleru se pri kreiranju
     * podrazumevano postavlja na danasnji datum ako ga korisnik ne posalje.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('paid_at')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
