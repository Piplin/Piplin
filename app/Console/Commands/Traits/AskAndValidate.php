<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Console\Commands\Traits;

use Symfony\Component\Console\Question\Question;

/**
 * A trait to add validation to console questions.
 **/
trait AskAndValidate
{
    /**
     * Asks a question and validates the response.
     *
     * @param  string   $question
     * @param  array    $choices
     * @param  function $validator
     * @param  mixed    $default
     * @param  bool     $secret
     * @return string
     */
    public function askAndValidate($question, array $choices, $validator, $default = null, $secret = false)
    {
        $question = new Question($question, $default);

        if ($secret) {
            $question->setHidden(true);
        }

        if (count($choices)) {
            $question->setAutocompleterValues($choices);
        }

        $question->setValidator($validator);

        return $this->output->askQuestion($question);
    }

    /**
     * Asks a question and validates the secret response.
     * @param  string   $question
     * @param  array    $choices
     * @param  function $validator
     * @param  mixed    $default
     * @return string
     */
    public function askSecretAndValidate($question, array $choices, $validator, $default = null)
    {
        return $this->askAndValidate($question, $choices, $validator, $default, true);
    }
}
