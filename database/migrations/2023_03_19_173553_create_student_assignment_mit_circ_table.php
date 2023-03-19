<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_assignment_mit_circ', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assignment')->constrained('student_assignment')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('mit_circ')->constrained('mit_circ')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_assignment_mit_circ');
    }
};
