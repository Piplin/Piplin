<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Test\Validators;

use Fixhub\Test\AbstractTestCase;
use Fixhub\Validators\RepositoryValidator;

class RepositoryValidatorTest extends AbstractTestCase
{
    /**
     * @dataProvider validationDataProvider
     */
    public function testValidate($value, $valid)
    {
        $validator = new RepositoryValidator;

        $result = $validator->validate('repository', $value, null);

        if ($valid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function validationDataProvider()
    {
        return [
            'Empty value'        => ['', false],
            'Null value'         => [null, false],
            'Protocol only'      => ['http:', false],
            'Hostname only'      => ['github.com', false],
            'IP address only'    => ['8.8.8.8', false],
            'HTTP host'          => ['http://github.com', true],
            'HTTPS host'         => ['https://github.com', true],
            'SSH host'           => ['ssh://github.com', true],
            'Git host'           => ['git://github.com', true],
            'Missing username'   => ['gitlab.com:namespace/repo.git', false],
            'Missing repo'       => ['git@gitlab.com', false],
            'Missing namespamce' => ['git@gitlab.com:repo.git', false],
            'Missing extension'  => ['git@gitlab.com:namespace/repo', false],
            'User repository'    => ['git@gitlab.com:namespace/repo.git', true],
        ];
    }
}
