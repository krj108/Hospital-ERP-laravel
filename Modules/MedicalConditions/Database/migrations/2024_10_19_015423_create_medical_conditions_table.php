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
    {        Schema::create('medical_conditions', function (Blueprint $table) {
        $table->id();
        $table->string('patient_national_id'); // Link to patient via national ID
        $table->text('condition_description'); // Free-text description of medical condition
        $table->foreignId('department_id')->constrained(); // Department linked to condition
        $table->foreignId('room_id')->constrained(); // Room based on availability
        $table->string('medications'); // Medications 
        $table->boolean('follow_up')->default(false); // Is follow-up required?
        $table->timestamp('follow_up_date')->nullable(); // Follow-up date and time
        $table->foreignId('doctor_id')->constrained(); // Doctor handling the condition
        $table->boolean('surgery_required')->default(false); // Is surgery required?
        $table->timestamps();
    });

    // Pivot table for Medical Conditions and Services
    Schema::create('medical_condition_service', function (Blueprint $table) {
        $table->id();
        $table->foreignId('medical_condition_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_conditions');
    }
};
