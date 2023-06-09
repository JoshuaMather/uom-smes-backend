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
        Schema::create('student_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student')->constrained('students')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('activity')->constrained('activity')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('week');
            $table->boolean('attended');
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
        Schema::dropIfExists('student_activity');
    }
};
