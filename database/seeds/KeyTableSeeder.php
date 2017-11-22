<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;
use Piplin\Models\Key;

class KeyTableSeeder extends Seeder
{
    private $private_key = <<<EOT
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAsj4nibtMB0pMLOnh9ltJtXrKMyRrI0j3eQhyCt37ya1o0FXJ
c0SD93PxTkCYQ9JEvRLO4P2Chzys8GQf5M8bnw7R9qlgIxIomm1ceQrLgaS9m/3X
3U3vtoGqQPorQHLYCNE2MiLuITqsKWdGk5Oeq8CCxx7lL+BWWryFmxBE2GFVxOWQ
jthWJ4d0WmUC9vVUqQMERxkfYWAM+2zDGgMqoQSGcGhvla7H9nCNussNRkLZADqK
bz3gfzVEYJzgKPmf749rBW+8M0jKK07EuXIaVjWZg2gAxLvhsb2H4jwc6QfTZYsK
dhTXowk4aJHEZ+q0jvpBRAYFSo9BMsE37Opd2QIDAQABAoIBAAsu+ywZJFwQvVbU
FelvMODCI42x60b7fQuLDBzCcNnml3z65Pjmg6EzFSDjzKn0t3tlgrV7MoVpuTAc
GCQzGniQZXwkhHOu3/1Qf0zY8Ivh2kO/WZv5F+FLoXMSP0R78DIdblkW9/n9xG+U
m0kHKh18prf3qEmoucJXscpY6vjoh7FgDEWNtNaDi+0VWm3JUQfZ+cjJtlchijiA
7eEaCC62D62/oDke3sGZRPzDQuBQUzpbgLq1ktWUSdErj1QnHvbyyJkGFhPF6Chu
VMnU7jLjVPf4DDy4SaAQn5CLviBAha/I+aa7FZupZ7p5zbJO1MVd/cZuiY8dnQxR
fjjSvkECgYEA4SIyuTJfUXg8NrKcdF2SeOLkjHrgAE7x6mcKOy+CLNF0em1j7Jo7
n1gN4y1/1HxmIJrbC6lWFc8FUCCqZADhnrweceLu0FfPDx45LL1tuE4HRWWgAU9O
70NLR1jlBkd20TX25Xn0LMzzG/9srEm6WsWpa7swI5/LFe4bZZcGc7MCgYEAyq4r
hFXf93j7ZnTm9/3CjwA0R8/9aLtHM+JiIi+9LRRnToOPGNmjekU7aG7lpCVODmR0
uCkfug5fzg7NmQ/JHjVTHkB4AyZvBtve+xZOl1XIPICU7zlgmUH6vfkyUKegk5k/
4kONeJxfnKrK74UBF8glzklQZ0qxe1WsI1/KkkMCgYB9tGugDqk8QydT3z0OmGS9
jOrSKZXDdlELccc7rtY/kiA4b7X4mVGrMi5m57PVgDRCBlVCWKXRfYtY5zRcrEVO
LXnOW0i/GhhqN5TzDz4hR6g1rn293XuUiv2Qc+lFLUgBnuEh1otpLHKd8mvKc0xT
fMSRe65wZbuungm8GqymkwKBgQCOvUjig0QfWnHbP1tmra123pLzPFTgjHxh2v53
yx01AVicH+UIgEY0l0t2ihtqlec6FZ9qD3RLqVHpod1D/a0LfEyUUpzywv+QWgrY
1GX3CK/jhD7fl44jgScg4b6AtP1O1a/7Hs4kciSYBmSzeP1DDW8qr9FTXXRPQw/f
PdhBVwKBgAQx9Kut/l47L282tMdpXbsjG5aesQ/r0OhIT0HleaH+xZDBGWGkD8JF
3kyIba9nTtZTCNDGawvSYIvioDb9ujIZbZoZVGxLnOL3P4Opb8mNxHg1t4/qrMY/
eDg2yesqbJUno+WBn205WaoeqxXJr7Amgx7/OU/PljKsj9r0w4Tu
-----END RSA PRIVATE KEY-----
EOT;

    private $public_key = <<<EOT
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCyPieJu0wHSkws6eH2W0m1esozJGsjSPd5CHIK3fvJrWjQVclzRIP3c/FOQJhD0kS9Es7g/YKHPKzwZB/kzxufDtH2qWAjEiiabVx5CsuBpL2b/dfdTe+2gapA+itActgI0TYyIu4hOqwpZ0aTk56rwILHHuUv4FZavIWbEETYYVXE5ZCO2FYnh3RaZQL29VSpAwRHGR9hYAz7bMMaAyqhBIZwaG+Vrsf2cI26yw1GQtkAOopvPeB/NURgnOAo+Z/vj2sFb7wzSMorTsS5chpWNZmDaADEu+GxvYfiPBzpB9Nliwp2FNejCThokcRn6rSO+kFEBgVKj0EywTfs6l3Z worker@piplin
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
