<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnvironmentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('environment_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('link_type');
            $table->unsignedInteger('environment_id');
            $table->unsignedInteger('opposite_environment_id');
            $table->timestamps();

            $table->unique(['environment_id', 'opposite_environment_id']);

            $table->foreign('environment_id')->references('id')->on('environments');
            $table->foreign('opposite_environment_id')->references('id')->on('environments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('environment_links');
    }
}
