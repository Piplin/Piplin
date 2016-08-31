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
use Fixhub\Validators\SSHKeyValidator;

class SSHKeyValidatorTest extends AbstractTestCase
{
    /**
     * @dataProvider validationDataProvider
     */
    public function testValidate($value, $valid)
    {
        $validator = new SSHKeyValidator;

        $result = $validator->validate('sshkey', $value, null);

        if ($valid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function validationDataProvider()
    {
        return [
            'Valid Key'          => [$this->getFixtureData('valid_rsa_key'), true],
            'Encrypted key'      => [$this->getFixtureData('encrypted_key'), false],
            'Missing header'     => [$this->getFixtureData('invalid_key_missing_header'), false],
            'Missing footer'     => [$this->getFixtureData('invalid_key_missing_footer'), false],
        ];
    }

    private function getFixtureData($file)
    {
        return file_get_contents(__DIR__ . '/fixtures/' . $file);
    }
}
