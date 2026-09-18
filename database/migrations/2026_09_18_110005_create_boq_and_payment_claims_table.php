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
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('item_code', 50);
            $table->text('description');
            $table->string('unit', 20);
            $table->decimal('total_quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('weight_percentage', 5, 2);
            $table->decimal('current_progress_percentage', 5, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payment_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('claim_number', 50);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('claimed_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->enum('status', ['submitted', 'certified', 'paid_partially', 'paid'])->default('submitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_claims');
        Schema::dropIfExists('boq_items');
    }
};
