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
        Schema::create('audit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coop_id')->constrained('cooperatives')->onDelete('cascade');
            $table->year('year');
            $table->binary('file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_reports');
    }
};
