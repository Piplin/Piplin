<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Key;
use Illuminate\Database\Seeder;

class KeyTableSeeder extends Seeder
{
    private $private_key = <<<EOT
-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAvfk0IThyGmEM07cJW/29eiblONRsOurT+Df6EGt1a4+BBg1M
fsAAEtmOhSxQ2MpSnhI8N5/8CQgUumb6tQRok68HJvWlPy/Aw1kmHpVcUTG0n9UI
Ak9I77auXE8SRXkXI/M5j9vXjvxDQ9fmIrxLaJQLjDRPH4Jr9g/z0u9fmoBAUcHQ
gNzLA7YHz8kzQEDS6mH3YNYDHBs1RorY6mKyhFH9juHYEZh/MPOWeFvWNPe01AYr
2hrhE0jgC29WmOUSX2z4JzZqK+7zZe61VSxde5c290Wyas3CFaVsHNkzW+Q5Ig3g
H+4gTom9UaflZkoQLyqPxMAt4T6XmlWFPKL+pQIDAQABAoIBAA73UfcIBl0zphoL
wm3/2GyGIerPOVOO6nIUntuqS47UuFpss8kMgTT69LJjIl9h2Q5g62OKdAWWIGPq
9vdJyJ9R26NjGMiYj3wUSt9/7szquIsa8k2UR2+zGZtmE09r0bngUHmX3SyDjR0M
JjI4WUx81UgPWuhlkvHIofHNL9/w9aG3rIAI+6K4iifYjtjDwkqMH/tXu+EIUDLp
HczzrqeFwIHZel/jY+ROu4kDNM4orxS0st1YyxbgS8IQWtaqZi/I3+sS3V8hUKYb
ycTTtdnlKUFJYNbceR6NZ5npJeYFrR9WNi1vNCNSPMC6i8dVR8/xw2+ilPUeFOGM
amzD9+ECgYEA+HGkbQx7MLSqu5Y4P4K0wztpLDakjD/uldp6algR58VnkUMrf0MD
C3uzZoRWQIeKM0FIDFtIu2SjNMvtBgnRjTMlgR05pF6UsjIyu0l43/6wwEb3pOaq
J/5aS5lJsnclSR3OgpF4fKq3oU0N2c/vDuWZiutiB3kMECdPrWL/o/kCgYEAw8BR
CzXVGWf52befMqVesIR1RZBAFJjUjQP4/kLoiYiAnDaXJdEfzRKV7NRmeuJb1645
D9/SxSjNTen4xlGhYtoduviYJUA0IslOZzK/zYkMMHTF8Iwn91s/HsNJswOboHvg
ScpJurhHt8+uZiIHjHUSgysUo0X7ly+GXXgJww0CgYA2s97+W8csHDuTfin4YfEn
I4euwoFMmC8SM77Md4PJwn9hTqbfKIQdHSmNIwpSvwVA79jLT7Yd/LSqxVP1Bmhr
bJ2PZj3w4RpgegkNj8nbmBqW24lfd6Jzl9+N0byWXQGKrdNwkFM1L+mqzGqGUPBU
GV3LZrR47MApNl6m0Kt1EQKBgDzCVuV54hkustinLBzWQ5vaoWPkMF+0SFU05HZX
YkI+Ql06fJPaY1qN6EdIbj66P/OkOkX5HTzhO0hx1SwJbmR2ez/rpZ36XbRmc5WI
pQww+72WoVHWzxjyE5eC2j9cYVPg329IALaaOHiPV/yPl3Q7anGYT6GWOU9mCvi8
J5uJAoGAZjV7H+8V/up8EgqnDelio45QseUtSnlhd+zxDD3ASblWMo2WJquJIEjl
ZboccgN9lBMXJSl+q5fw0ngO7l68qu2i0VW/BOFLwbYz/QTlLDUlL63jDyNrZ/s6
5GTqZDKQ6psXyFkJxV0KayKfkjbQp2N8+iUl/AJ7Tsw7CpW4IoA=
-----END RSA PRIVATE KEY-----
EOT;

    private $public_key = <<<EOT
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQC9+TQhOHIaYQzTtwlb/b16JuU41Gw66tP4N/oQa3Vrj4EGDUx+wAAS2Y6FLFDYylKeEjw3n/wJCBS6Zvq1BGiTrwcm9aU/L8DDWSYelVxRMbSf1QgCT0jvtq5cTxJFeRcj8zmP29eO/END1+YivEtolAuMNE8fgmv2D/PS71+agEBRwdCA3MsDtgfPyTNAQNLqYfdg1gMcGzVGitjqYrKEUf2O4dgRmH8w85Z4W9Y097TUBivaGuETSOALb1aY5RJfbPgnNmor7vNl7rVVLF17lzb3RbJqzcIVpWwc2TNb5DkiDeAf7iBOib1Rp+VmShAvKo/EwC3hPpeaVYU8ov6l fixhub@fixhub.org
EOT;

    public function run()
    {
        DB::table('keys')->delete();

        Key::create([
            'name'        => 'Default Key',
            'private_key' => $this->private_key,
            'public_key'  => $this->public_key,
        ]);
    }
}
