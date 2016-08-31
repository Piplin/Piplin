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

use Fixhub\Bus\Jobs\RequestProjectCheckUrl;
use Fixhub\Models\CheckUrl as CheckUrlModel;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Schedule the url check.
 */
class CheckUrl extends Command
{
    use DispatchesJobs;

    const URLS_TO_CHECK = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixhub:checkurls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request the project check URL and notify when failed.';

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
        $period = [];

        $minute = intval(date('i'));
        if ($minute === 0) {
            $period = [60, 30, 10, 5];
        } else {
            if ($minute % 30 === 0) {
                $period = [30, 10, 5];
            } elseif ($minute % 10 === 0) {
                $period = [10, 5];
            } elseif ($minute % 5 === 0) {
                $period = [5];
            }
        }

        if (empty($period)) {
            return true;
        }

        CheckUrlModel::whereIn('period', $period)->chunk(self::URLS_TO_CHECK, function ($urls) {
            $this->dispatch(new RequestProjectCheckUrl($urls));
        });
    }
}
