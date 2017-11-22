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
use Illuminate\Support\Facades\Validator;
use Piplin\Bus\Events\UserWasCreatedEvent;
use Piplin\Models\User;
use RuntimeException;

/**
 * A command to create a user.
 **/
class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piplin:create-user
                            {name : The name for the user}
                            {email : The email address for the user}
                            {password? : The password for the user, one will be generated if not supplied}
                            {--no-email : Do not send a welcome email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user';

    /**
     * Create a new command instance..
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
        $arguments  = $this->argument();
        $send_email = (!$this->option('no-email'));

        $password_generated = false;
        if (!$arguments['password']) {
            $arguments['password'] = str_random(15);
            $password_generated    = true;
        }

        $validator = Validator::make($arguments, [
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if (!$validator->passes()) {
            throw new RuntimeException($validator->errors()->first());
        }

        $originalPassword = $arguments['password'];

        $arguments['password'] = bcrypt($arguments['password']);

        $user = User::create($arguments);

        $message = 'The user has been created';

        if ($send_email) {
            $message = 'The user has been created and their account details have been emailed to ' . $user->email;

            event(new UserWasCreatedEvent($user, $originalPassword));
        } elseif ($password_generated) {
            $message .= ', however you elected to not email the account details to them. ';
            $message .= 'Their password is ' . $originalPassword;
        }

        $this->info($message);
    }
}
