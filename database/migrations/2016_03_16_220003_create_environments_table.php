<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\Project;

class CreateEnvironmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('environments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('order')->default(0);
            $table->integer('targetable_id');
            $table->string('targetable_type');
            $table->boolean('default_on')->default(true);
            $table->tinyInteger('status')->default(Project::NOT_DEPLOYED);
            $table->dateTime('last_run')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['targetable_id', 'targetable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('environments');
    }
}
