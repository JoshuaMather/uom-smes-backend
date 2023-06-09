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
        Schema::create('student_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student')->constrained('students')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('assignment')->constrained('assignment')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('date_submitted')->nullable();
            $table->float('grade')->nullable();
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
        Schema::dropIfExists('student_assignment');
    }
};
