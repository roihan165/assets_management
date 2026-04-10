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
        Schema::create('loan_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_unit_id')->constrained()->cascadeOnDelete();
            
            $table->enum('condition_before', [
                'good',
                'minor_damage',
                'major_damage',
                'lost'
            ])->nullable();
            
            $table->enum('condition_after', [
                'good',
                'minor_damage',
                'major_damage',
                'lost'
            ])->nullable();
            
            $table->text('condition_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_details');
    }
};
