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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 191);
            $table->string('document_code', 100);
            $table->enum('discipline', [
                'architectural',
                'structural',
                'mechanical',
                'electrical',
                'contracts',
                'permits',
            ]);
            $table->string('current_revision', 10)->default('Rev 00');
            $table->string('file_path', 255);
            $table->boolean('is_approved_for_construction')->default(false);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
