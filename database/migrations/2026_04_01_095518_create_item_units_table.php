<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->string('code')->unique();

            $table->enum('status', [
                'available',
                'borrowed',
                'maintenance',
                'lost'
            ])->default('available');

            $table->enum('condition_status', [
                'good',
                'minor_damage',
                'major_damage',
                'lost'
            ])->default('good');

            $table->text('condition_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
