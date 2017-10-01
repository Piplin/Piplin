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

use DateTimeZone;
use Fixhub\Console\Commands\Traits\AskAndValidate;
use Fixhub\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use PDO;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Process\Process;

/**
 * A console command for prompting for install details.
 */
class InstallApp extends Command
{
    use AskAndValidate;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the application and configures the settings';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->verifyNotInstalled()) {
            return -1;
        }

        $this->clearCaches();

        $config = base_path('.env');

        if (!file_exists($config)) {
            copy(base_path('.env.example'), $config);
            Config::set('app.key', 'SomeRandomString');
        }

        $this->line('');
        $this->info('***********************');
        $this->info('  Welcome to Fixhub  ');
        $this->info('***********************');
        $this->line('');

        if (!$this->checkRequirements()) {
            return -1;
        }

        $this->line('Please answer the following questions:');
        $this->line('');

        $config = [
            'db'   => $this->getDatabaseInformation(),
            'app'  => $this->getInstallInformation(),
            'mail' => $this->getEmailInformation(),
        ];

        $admin = $this->getAdminInformation();

        $config['jwt']['secret'] = $this->generateJWTKey();

        $this->writeEnvFile($config);

        $this->generateKey();
        $this->migrate();

        $user = User::where('level', User::LEVEL_ADMIN)->first();
        $user->update($admin);

        if ($this->getLaravel()->environment() === 'local') {
            $this->seed();
        }

        $this->optimize();

        $this->line('');
        $this->info('Success! Fixhub is now installed');
        $this->line('');
        $this->header('Next steps');
        $this->line('');
        $this->line('Example configuration files can be found in the "examples" directory');
        $this->line('');
        $this->comment('1. Set up your web server, see either "nginx.conf" or "apache.conf"');
        $this->line('');
        $this->comment('2. Setup the cronjobs, see "crontab"');
        $this->line('');
        $this->comment('3. Setup the socket server & queue runner, see "supervisor.conf" for an example commands');
        $this->line('');
        $this->comment('4. Ensure that "storage" and "public/upload" are writable by the webserver');
        $this->line('');
        $this->comment('5. Visit ' . $config['app']['url'] . ' and login with the details you provided to get started');
        $this->line('');
    }

    /**
     * Writes the configuration data to the config file.
     *
     * @param array $input The config data to write
     *
     * @return bool
     */
    protected function writeEnvFile(array $input)
    {
        $this->info('Writing configuration file');
        $this->line('');

        $path   = base_path('.env');
        $config = file_get_contents($path);

        // Move the socket value to the correct key
        if (isset($input['app']['socket'])) {
            $input['socket']['url'] = $input['app']['socket'];
            unset($input['app']['socket']);
        }

        if (isset($input['app']['ssl'])) {
            foreach ($input['app']['ssl'] as $key => $value) {
                $input['socket']['ssl_' . $key] = $value;
            }

            unset($input['app']['ssl']);
        }

        foreach ($input as $section => $data) {
            foreach ($data as $key => $value) {
                $env = strtoupper($section . '_' . $key);

                $config = preg_replace('/' . $env . '=(.*)/', $env . '=' . $value, $config);
            }
        }

        // Remove SSL certificate keys if not using HTTPS
        if (substr($input['socket']['url'], 0, 5) !== 'https') {
            foreach (['key', 'cert', 'ca'] as $key) {
                $key = strtoupper($key);

                $config = preg_replace('/SOCKET_SSL_' . $key . '_FILE=(.*)[\n]/', '', $config);
            }
        }

        // Remove keys not needed for sqlite
        if (isset($input['db']['type']) && $input['db']['type'] === 'sqlite') {
            foreach (['host', 'database', 'username', 'password'] as $key) {
                $key = strtoupper($key);

                $config = preg_replace('/DB_' . $key . '=(.*)[\n]/', '', $config);
            }
        }

        // Remove keys not needed by SMTP
        if ($input['mail']['driver'] !== 'smtp') {
            foreach (['host', 'port', 'username', 'password'] as $key) {
                $key = strtoupper($key);

                $config = preg_replace('/MAIL_' . $key . '=(.*)[\n]/', '', $config);
            }
        }

        // Remove github keys if not needed, only really exists on my dev copy
        if (!isset($input['github']) || empty($input['github']['oauth_token'])) {
            $config = preg_replace('/GITHUB_OAUTH_TOKEN=(.*)[\n]/', '', $config);
        }

        // Remove trusted_proxies if not set
        if (!isset($input['trusted']) || !isset($input['trusted']['proxied'])) {
            $config = preg_replace('/TRUSTED_PROXIES=(.*)[\n]/', '', $config);
        }

        return file_put_contents($path, trim($config) . PHP_EOL);
    }

    /**
     * Calls the artisan key:generate to set the APP_KEY.
     */
    private function generateKey()
    {
        $this->info('Generating application key');
        $this->line('');
        $this->call('key:generate');
    }

    /**
     * Generates a key for JWT.
     *
     * @return string
     */
    protected function generateJWTKey()
    {
        $this->info('Generating JWT key');
        $this->line('');
        //$this->call('jwt:generate'); This does not update .ENV so do it manually for now

        return str_random(32);
    }

    /**
     * Calls the artisan migrate to set up the database
     *
     * @param bool $seed Whether or not to seed the database
     */
    protected function migrate()
    {
        $this->info('Running database migrations');
        $this->line('');
        $this->call('migrate', ['--force' => true]);
        $this->line('');
    }

    /**
     * Calls the artisan db:seed to seed the database
     *
     * @param bool $seed Whether or not to seed the database
     */
    protected function seed()
    {
        $this->info('Seeding database');
        $this->line('');
        $this->call('db:seed', ['--force' => true]);
        $this->line('');
    }

    /**
     * Clears all Laravel caches.
     */
    protected function clearCaches()
    {
        $this->call('clear-compiled');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('config:clear');
        $this->call('view:clear');
    }

    /**
     * Runs the artisan optimize commands.
     */
    protected function optimize()
    {
        $this->clearCaches();

        if ($this->getLaravel()->environment() !== 'local') {
            $this->call('optimize', ['--force' => true]);
            $this->call('config:cache');
            $this->call('route:cache');
        }
    }

    /**
     * Prompts the user for the database connection details.
     *
     * @return array
     */
    private function getDatabaseInformation()
    {
        $this->header('Database details');

        $connectionVerified = false;

        while (!$connectionVerified) {
            $database = [];

            $drivers = $this->getDatabaseDrivers();

            // Should we just skip this step if only one driver is available?
            $type = $this->choice('Type', $drivers, (int)array_search('mysql', $drivers, true));

            $database['type'] = $type;

            Config::set('database.default', $type);

            if ($type !== 'sqlite') {
                $host = $this->ask('Host', '127.0.0.1');
                $name = $this->ask('Database', 'fixhub');
                $user = $this->ask('Username', 'fixhub');
                $pass = $this->secret('Password');

                $database['host']     = $host;
                $database['database'] = $name;
                $database['username'] = $user;
                $database['password'] = $pass;

                Config::set('database.connections.' . $type . '.host', $host);
                Config::set('database.connections.' . $type . '.database', $name);
                Config::set('database.connections.' . $type . '.username', $user);
                Config::set('database.connections.' . $type . '.password', $pass);
            }

            $connectionVerified = $this->verifyDatabaseDetails($database);
        }

        return $database;
    }

    /**
     * Prompts the user for the basic setup information.
     *
     * @return array
     */
    private function getInstallInformation()
    {
        $this->header('Installation details');

        $regions = $this->getTimezoneRegions();
        $locales = $this->getLocales();

        $url_callback = function ($answer) {
            $validator = Validator::make(['url' => $answer], [
                'url' => 'url',
            ]);

            if (!$validator->passes()) {
                throw new \RuntimeException($validator->errors()->first('url'));
            }

            return preg_replace('#/$#', '', $answer);
        };

        $url    = $this->askAndValidate('Application URL ("http://fixhub.app" for example)', [], $url_callback, 'http://fixhub.app');
        $region = $this->choice('Timezone region', array_keys($regions), 4);

        if ($region !== 'UTC') {
            $locations = $this->getTimezoneLocations($regions[$region]);

            $region .= '/' . $this->choice('Timezone location', $locations, 63);
        }

        $socket = $this->askAndValidate('Socket URL', [], $url_callback, $url);

        // If the URL doesn't have : in twice (the first is in the protocol, the second for the port)
        if (substr_count($socket, ':') === 1) {
            // Check if running on nginx, and if not then add it
            $process = new Process('which nginx');
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                $socket .= ':6001';
            }
        }

        $path_callback = function ($answer) {
            $validator = Validator::make(['path' => $answer], [
                'path' => 'required',
            ]);

            if (!$validator->passes()) {
                throw new \RuntimeException($validator->errors()->first('path'));
            }

            if (!file_exists($answer)) {
                throw new \RuntimeException('File does not exist');
            }

            return $answer;
        };

        $ssl = null;
        if (substr($socket, 0, 5) === 'https') {
            $ssl = [
                'key_file'  => $this->askAndValidate('SSL key File', [], $path_callback),
                'cert_file' => $this->askAndValidate('SSL certificate File', [], $path_callback),
                'ca_file'   => $this->askAndValidate('SSL certificate authority file', [], $path_callback),
            ];
        };

        // If there is only 1 locale just use that
        if (count($locales) === 1) {
            $locale = $locales[0];
        } else {
            $default = array_search(Config::get('app.fallback_locale'), $locales, true);
            $locale  = $this->choice('Language', $locales, $default);
        }

        return [
            'url'      => $url,
            'timezone' => $region,
            'socket'   => $socket,
            'ssl'      => $ssl,
            'locale'   => $locale,
        ];
    }

    /**
     * Prompts the user for the details for the email setup.
     *
     * @return array
     */
    private function getEmailInformation()
    {
        $this->header('Email details');

        $email = [];

        $driver = $this->choice('Type', ['smtp', 'sendmail', 'mail'], 2);

        if ($driver === 'smtp') {
            $host = $this->ask('Host', 'localhost');

            $port = $this->askAndValidate('Port', [], function ($answer) {
                $validator = Validator::make(['port' => $answer], [
                    'port' => 'integer',
                ]);

                if (!$validator->passes()) {
                    throw new \RuntimeException($validator->errors()->first('port'));
                };

                return $answer;
            }, 25);

            $user = $this->ask('Username');
            $pass = $this->secret('Password');

            $email['host']     = $host;
            $email['port']     = $port;
            $email['username'] = $user;
            $email['password'] = $pass;
        }

        $from_name = $this->ask('From name', 'Fixhub');

        $from_address = $this->askAndValidate('From address', [], function ($answer) {
            $validator = Validator::make(['from_address' => $answer], [
                'from_address' => 'email',
            ]);

            if (!$validator->passes()) {
                throw new \RuntimeException($validator->errors()->first('from_address'));
            };

            return $answer;
        }, 'fixhub@fixhub.app');

        $email['from_name']    = $from_name;
        $email['from_address'] = $from_address;
        $email['driver']       = $driver;

        return $email;
    }

    /**
     * Prompts for the admin user details.
     *
     * @return array
     */
    private function getAdminInformation()
    {
        $this->header('Admin details');

        $name = $this->ask('Name', 'Admin');

        $email_address = $this->askAndValidate('Email address', [], function ($answer) {
            $validator = Validator::make(['email_address' => $answer], [
                'email_address' => 'email',
            ]);

            if (!$validator->passes()) {
                throw new \RuntimeException($validator->errors()->first('email_address'));
            };

            return $answer;
        });

        $password = $this->askSecretAndValidate('Password', [], function ($answer) {
            $validator = Validator::make(['password' => $answer], [
                'password' => 'min:6',
            ]);

            if (!$validator->passes()) {
                throw new \RuntimeException($validator->errors()->first('password'));
            };

            return $answer;
        });

        return [
            'name'     => $name,
            'email'    => $email_address,
            'password' => bcrypt($password),
        ];
    }

    /**
     * Verifies that the database connection details are correct.
     *
     * @param array $database The connection details
     *
     * @return bool
     */
    private function verifyDatabaseDetails(array $database)
    {
        if ($database['type'] === 'sqlite') {
            return touch(database_path('database.sqlite'));
        }

        try {
            $connection = new PDO(
                $database['type'] . ':host=' . $database['host'] . ';dbname=' . $database['database'],
                $database['username'],
                $database['password'],
                [
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT    => 2,
                ]
            );

            unset($connection);

            return true;
        } catch (\Exception $error) {
            $this->block([
                'Fixhub could not connect to the database with the details provided. Please try again.',
                PHP_EOL,
                $error->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Ensures that Fixhub has not been installed yet.
     *
     * @return bool
     */
    private function verifyNotInstalled()
    {
        if (config('app.key') !== false && config('app.key') !== 'SomeRandomString') {
            $this->block([
                'You have already installed Fixhub!',
                PHP_EOL,
                'If you were trying to update Fixhub, please use "php artisan app:update" instead.',
            ]);

            return false;
        }

        return true;
    }

    /**
     * Checks the system meets all the requirements needed to run Fixhub.
     *
     * @return bool
     */
    protected function checkRequirements()
    {
        $errors = false;

        // Check PHP version:
        if (!version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $this->error('PHP 7.0.0 or higher is required');
            $errors = true;
        }

        // Check for required PHP extensions
        $required_extensions = ['PDO', 'curl', 'gd', 'json',
                                'tokenizer', 'openssl', 'mbstring',
                               ];

        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $this->error('Extension required: ' . $extension);
                $errors = true;
            }
        }

        if (!count($this->getDatabaseDrivers())) {
            $this->error(
                'At least 1 PDO driver is required. Either sqlite, mysql, pgsql or sqlsrv, check your php.ini file'
            );
            $errors = true;
        }

        // Functions needed by symfony process
        $required_functions = ['proc_open'];

        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                $this->error('Function required: ' . $function . '. Is it disabled in php.ini?');
                $errors = true;
            }
        }

        // Programs needed in $PATH
        $required_commands = ['ssh', 'ssh-keygen', 'git', 'scp', 'tar', 'gzip', 'rsync', 'bash'];

        foreach ($required_commands as $command) {
            $process = new Process('which ' . $command);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->error('Program not found in path: ' . $command);
                $errors = true;
            }
        }

        $required_one = ['node', 'nodejs'];
        $found        = false;
        foreach ($required_one as $command) {
            $process = new Process('which ' . $command);
            $process->setTimeout(null);
            $process->run();
            if ($process->isSuccessful()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->error('node.js was not found');
            $errors = true;
        }

        // Files and directories which need to be writable
        $writable = ['.env', 'storage', 'storage/logs', 'storage/app', 'storage/app/mirrors',
                     'storage/framework', 'storage/framework/cache', 'storage/framework/sessions',
                     'storage/framework/views', 'bootstrap/cache', 'public/upload',
                    ];

        foreach ($writable as $path) {
            if (!is_writeable(base_path($path))) {
                $this->error($path . ' is not writeable');
                $errors = true;
            }
        }

        // Check that redis is running
        try {
            Redis::connection()->ping();
        } catch (\Exception $e) {
            $this->error('Redis is not running');
            $errors = true;
        }

        if (isset($_ENV['QUEUE_DRIVER']) && $_ENV['QUEUE_DRIVER'] === 'beanstalkd') {
            $connected = Queue::connection()->getPheanstalk()
                                            ->getConnection()
                                            ->isServiceListening();

            if (!$connected) {
                $this->error('Beanstalkd is not running');
                $errors = true;
            }
        }

        if ($errors) {
            $this->line('');
            $this->block('Fixhub cannot be installed. Please review the errors above before continuing.');
            $this->line('');

            return false;
        }

        return true;
    }

    /**
     * Gets an array of available PDO drivers which are supported by Laravel.
     *
     * @return array
     */
    private function getDatabaseDrivers()
    {
        $available = collect(PDO::getAvailableDrivers());

        return $available->intersect(['mysql', 'sqlite', 'pgsql', 'sqlsrv'])
                         ->all();
    }

    /**
     * Gets a list of timezone regions.
     *
     * @return array
     */
    private function getTimezoneRegions()
    {
        return [
            'UTC'        => DateTimeZone::UTC,
            'Africa'     => DateTimeZone::AFRICA,
            'America'    => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Asia'       => DateTimeZone::ASIA,
            'Atlantic'   => DateTimeZone::ATLANTIC,
            'Australia'  => DateTimeZone::AUSTRALIA,
            'Europe'     => DateTimeZone::EUROPE,
            'Indian'     => DateTimeZone::INDIAN,
            'Pacific'    => DateTimeZone::PACIFIC,
        ];
    }

    /**
     * Gets a list of available locations in the supplied region.
     *
     * @param int $region The region constant
     *
     * @return array
     *
     * @see DateTimeZone
     */
    private function getTimezoneLocations($region)
    {
        $locations = [];

        foreach (DateTimeZone::listIdentifiers($region) as $timezone) {
            $locations[] = substr($timezone, strpos($timezone, '/') + 1);
        }

        return $locations;
    }

    /**
     * Gets a list of the available locales.
     *
     * @return array
     */
    private function getLocales()
    {
        // Get the locales from the files on disk
        $locales = File::directories(base_path('resources/lang/'));

        array_walk($locales, function (&$locale) {
            $locale = basename($locale);
        });

        return $locales;
    }

    /**
     * A wrapper around symfony's formatter helper to output a block.
     *
     * @param string|array $messages Messages to output
     * @param string       $type     The type of message to output
     */
    protected function block($messages, $type = 'error')
    {
        $output = [];

        if (!is_array($messages)) {
            $messages = (array) $messages;
        }

        $output[] = '';

        foreach ($messages as $message) {
            $output[] = trim($message);
        }

        $output[] = '';

        $formatter = new FormatterHelper();
        $this->line($formatter->formatBlock($output, $type));
    }

    /**
     * Outputs a header block.
     *
     * @param string $header The text to output
     */
    protected function header($header)
    {
        $this->block($header, 'question');
    }
}
