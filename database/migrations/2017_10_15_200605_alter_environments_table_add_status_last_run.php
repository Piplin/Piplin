<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\Project;

class AlterEnvironmentsTableAddStatusLastRun extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('environments', function (Blueprint $table) {
            $table->tinyInteger('status')->default(Project::NOT_DEPLOYED)->after('default_on');
            $table->dateTime('last_run')->nullable()->default(null)->after('default_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('environments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('last_run');
        });
    }
}
