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
        Schema::create('tumanlar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name_uz');
            $table->string('name_ru')->nullable();
            $table->string('name_en')->nullable();
            $table->string('viloyat_soato')->default('0');
            $table->string('soato');
            $table->boolean('is_active')->default(true);
            $table->foreignId('viloyat_id')->constrained('viloyatlar')->onDelete('cascade');
            $table->uuid('old_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tumanlar');
    }
};
