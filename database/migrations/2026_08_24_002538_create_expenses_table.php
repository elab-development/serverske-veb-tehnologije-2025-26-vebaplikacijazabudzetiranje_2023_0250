<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() = kreira tabelu "expenses"
     *
     * Svaki trošak pripada tačno jednoj grupi (grupe pravi timska drugarica
     * u svojoj grani - "groups" tabela mora postojati pre ove migracije)
     * i ima tačno jednog korisnika koji ga je platio ("paid_by").
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->decimal('amount', 10, 2);

            // NAPOMENA: kolona "category" je ovde obična string kolona.
            // Kasnija migracija (change_category_column_in_expenses_table) je
            // menja u enum - demonstracija "izmena postojeće kolone".
            $table->string('category', 50)->default('other');

            // NAPOMENA: kolona "notes" se namerno dodaje ovde da bi kasnija
            // migracija (drop_notes_from_expenses_table) demonstrirala brisanje kolone
            $table->text('notes')->nullable();

            $table->text('description')->nullable();
            $table->date('expense_date');

            $table->timestamps();
        });
    }

    /**
     * down() = uklanja tabelu "expenses"
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
