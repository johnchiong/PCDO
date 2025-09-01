<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cooperative_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cooperative_id');
            $table->unsignedBigInteger('checklist_item_id');
            $table->string('file_name');
            $table->string('mime_type');
            $table->binary('file_content'); // store file in DB
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coop_uploads');
    }
};