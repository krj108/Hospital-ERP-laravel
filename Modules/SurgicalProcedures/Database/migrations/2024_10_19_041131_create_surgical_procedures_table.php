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
        Schema::create('surgical_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_condition_id')->constrained('medical_conditions'); // Link to medical condition
            $table->string('surgery_type'); // Type of surgery
            $table->foreignId('department_id')->constrained(); // Department of surgery
            $table->foreignId('room_id')->constrained(); // Room for surgery
            $table->text('medical_staff'); // Change from json to text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_procedures');
    }
};
