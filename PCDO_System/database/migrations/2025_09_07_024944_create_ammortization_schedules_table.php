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
        Schema::create('ammortization_schedules', function (Blueprint $table) {
            $table->foreignId('coop_program_id')->constrained('coop_programs')->onDelete('cascade');
            $table->date('due_date');
            $table->integer('installment');
            $table->dateTime('date_paid');
            $table->integer('ammount_paid');
            $table->enum('status', ['Unpaid', 'Paid', 'Near Due', 'Overdue'])->default('Unpaid');
            $table->integer('penalty_ammount');
            $table->integer('balance');
            $table->string('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ammortization_schedules');
    }
};
