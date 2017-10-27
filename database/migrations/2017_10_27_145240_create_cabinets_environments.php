<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCabinetsEnvironments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cabinet_environment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cabinet_id');
            $table->unsignedInteger('environment_id');
            $table->tinyInteger('status')->default(1);

            $table->foreign('cabinet_id')->references('id')->on('cabinets');
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
        Schema::dropIfExists('cabinet_environment');
    }
}
