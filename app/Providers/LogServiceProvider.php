<?php

namespace Fixhub\Providers;

use Illuminate\Log\LogServiceProvider as ServiceProvider;
use Illuminate\Log\Writer;

/**
 * Custom log service provider. Override the built in one to allow custom log file names.
 *
 * This works as the register method registers a singleton, so when this
 * service provider is registered it replaced the default one.
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles($this->getFileName(), $this->logLevel());
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles($this->getFileName(), $this->maxFiles(), $this->logLevel());
    }

    /**
     * Determines the filename for the log.
     *
     * @return string
     */
    private function getFileName()
    {
        return $this->app->storagePath() . '/logs/' . php_sapi_name() . '.log';
    }
}
