<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Piplin\Models\ConfigFile;

class AlterConfigFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_files', function (Blueprint $table) {
            $table->tinyInteger('status')->default(ConfigFile::UNSYNCED);
            $table->dateTime('last_run')->nullable()->default(null);
            $table->longtext('output')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_files', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('last_run');
            $table->dropColumn('output');
        });
    }
}
