<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsEnvironmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_environment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('command_id');
            $table->unsignedInteger('environment_id');

            $table->foreign('command_id')->references('id')->on('commands');
            $table->foreign('environment_id')->references('id')->on('environments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('command_environment');
    }
}
