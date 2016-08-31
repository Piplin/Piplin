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
use Fixhub\Validators\ChannelValidator;

class ChannelValidatorTest extends AbstractTestCase
{
    /**
     * @dataProvider validationDataProvider
     */
    public function testValidate($value, $valid)
    {
        $validator = new ChannelValidator;

        $result = $validator->validate('channel', $value, null);

        if ($valid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function validationDataProvider()
    {
        return [
            'Empty value'    => ['', false],
            'Null value'     => [null, false],
            'No prefix'      => ['channel', false],
            'Invalid prefix' => ['$channel', false],
            'Only hash'      => ['#', false],
            'Only at'        => ['@', false],
            'Valid channel'  => ['#channel', true],
            'Valid person'   => ['@username', true],
        ];
    }
}
