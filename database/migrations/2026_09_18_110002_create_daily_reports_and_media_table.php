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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('report_date');
            $table->string('weather_condition', 50)->nullable();
            $table->unsignedInteger('manpower_count')->default(0);
            $table->text('work_summary');
            $table->text('blockers_notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('submitted');
            $table->timestamps();

            $table->unique(['project_id', 'report_date'], 'project_date_unique');
        });

        Schema::create('daily_report_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('thumbnail_path', 255)->nullable();
            $table->enum('file_type', ['image', 'video'])->default('image');
            $table->string('caption', 191)->nullable();
            $table->decimal('geo_latitude', 10, 8)->nullable();
            $table->decimal('geo_longitude', 11, 8)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_media');
        Schema::dropIfExists('daily_reports');
    }
};
