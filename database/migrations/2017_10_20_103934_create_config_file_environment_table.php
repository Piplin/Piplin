<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigFileEnvironmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_file_environment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('config_file_id');
            $table->unsignedInteger('environment_id');

            $table->foreign('config_file_id')->references('id')->on('config_files');
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
        Schema::dropIfExists('config_file_environment');
    }
}
