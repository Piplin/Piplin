<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Validators;

/**
 * Class for validating git repository URLs.
 */
class RepositoryValidator
{
    /**
     * Validate that the repository URL looks valid.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($attribute, $value, $parameters)
    {
        if (preg_match('/^(ssh|git|https?):\/\//', $value)) { // Plain old git repo
            return true;
        }

        if (preg_match('/^(.*)@(.*):(.*)\/(.*)\.git/', $value)) { // Gitlab
            return true;
        }

        return false;
    }
}
