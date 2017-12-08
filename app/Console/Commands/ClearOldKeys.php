<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Console\Commands;

use Illuminate\Console\Command;

/**
 * Clears out any temp SSH keys and wrapper scripts which have been left on disk.
 */
class ClearOldKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piplin:purge-temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears out any temp SSH key files and wrapper scripts.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Clear out old SSH key files
        $files   = glob(storage_path('app/') . '*ssh*'); // sshkey and gitssh
        $folders = glob(storage_path('app/') . '*clone*'); // cloned copies of code

        $this->info('Found ' . count($files) . ' files and ' . count($folders) . ' folders to purge');

        // Now loop through the temp files and delete them from storage
        foreach (array_merge($files, $folders) as $path) {
            $file = basename($path);

            // Don't delete recently created files as a precaution, 12 hours is more than enough
            if (filemtime($path) > strtotime('-12 hours')) {
                $this->info('Skipping ' . $file);
                continue;
            }

            $success = true;

            if (is_dir($path)) {
                if (!rmdir($path)) {
                    $this->error('Failed to delete folder ' . $file);
                    $success = false;
                }
            } elseif (!unlink($path)) {
                $this->error('Failed to delete file ' . $file);
                $success = false;
            }

            if ($success) {
                $this->info('Deleted ' . $file);
            }
        }
    }
}
