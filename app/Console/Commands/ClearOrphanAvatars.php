<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Checks for and cleans up orphaned avatar files.
 */
class ClearOrphanAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixhub:purge-avatars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges out avatar images which are no longer in use by an account';

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
        $this->purgeOldAvatars();
    }

    /**
     * Remove unused avatar files from disk.
     *
     * @return void
     */
    private function purgeOldAvatars()
    {
        // Build up a list of all avatar images
        $avatars = glob(public_path() . '/upload/*/*.*');

        // Remove the public_path() from the path so that they match values in the DB
        array_walk($avatars, function (&$avatar) {
            $avatar = str_replace(public_path(), '', $avatar);
        });

        $all_avatars = collect($avatars);

        // Get all avatars currently assigned
        $current_avatars = DB::table('users')
                             ->whereNotNull('avatar')
                             ->pluck('avatar');

        // Compare the 2 collections get a list of avatars which are no longer assigned
        $orphan_avatars = $all_avatars->diff($current_avatars);

        $this->info('Found ' . $orphan_avatars->count() . ' orphaned avatars');

        // Now loop through the avatars and delete them from storage
        foreach ($orphan_avatars as $avatar) {
            $avatarPath = public_path() . $avatar;

            // Don't delete recently created files as they could be temp files from the uploader
            if (filemtime($avatarPath) > strtotime('-15 minutes')) {
                $this->info('Skipping ' . $avatar);
                continue;
            }

            if (!unlink($avatarPath)) {
                $this->error('Failed to delete ' . $avatar);
            } else {
                $this->info('Deleted ' . $avatar);
            }
        }
    }
}
