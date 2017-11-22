<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patterns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('copy_pattern');
            $table->unsignedInteger('build_plan_id');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('build_plan_id')->references('id')->on('build_plans');
        });

        Schema::create('command_pattern', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('command_id');
            $table->unsignedInteger('pattern_id');

            $table->foreign('command_id')->references('id')->on('commands');
            $table->foreign('pattern_id')->references('id')->on('patterns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patterns');
        Schema::dropIfExists('command_pattern');
    }
}
