<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Project;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('projects')->delete();

        Project::create([
            'name'              => 'Fixhub',
            'hash'              => str_random(60),
            'repository'        => 'https://github.com/fixhub/fixhub.git',
            'url'               => 'http://fixhub.app',
            'group_id'          => 2,
            'private_key'       => '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAmrMjtajVvmd99T8xwUNrIFbrzSmZ6VCM89hfm4Ut9atv29gG
l2HFPJY7VtslXDJVL67w5EUMspy82tkAX7F03iaarSsbo6nC16UTfbfNTi44Snm0
T/5RMavSOnOMRJ8BQcfzqge4oIQzVGXOs0YvNFdSt4paBp9dssKS+7yP/hDvgAVz
+LE3IcIeO26aXATcuB4zq3vjaqSzWZGdNhOJZ4EmjgmOq9+k3SAmooHkF+p/14MJ
tq0ZK9KjSGbHfyKMi2EuvwllFCY19eqsV7dcMDIsMKUW2diFC52dJSO+EF47nA/j
sNDisFsIC7DeeVVBl1TpaV9RidqeZmdx+mF9AQIDAQABAoIBAH4qhYAdTx03eGGw
hVqSKmc4nJ05RX4kJKCmoerLZh1LETJh75Y8tchg2cpPdhvILPNzoKD6s41kCR4P
BqAEsUSQhWufka4bwH1w8wGACp+tUFllAqqOxhdVg2IKZKZ+a18DvPS50ViQGPDH
CxnorozoftyTqDJofNlSmN9X/LN+RZ1zRJRaPkBvSkYOCT4gnJLmHLGN7eJsHQeR
EJe83E4VPZ+2faBHEigXAHc4rh63iRxmmqqlcrItXzONZZUOjXwBNqZs4aVl+DZd
1pPiB9nOT9zLiy9ZwHZfIRIF3LkWAVsIkzOPDw9wLNgzI60uiLlYY1ODua8maqDP
m5eOT7ECgYEAzbVdEVngZd/jRlAo/LOLyy6NbZP4fli26hZjJBAJ8HhI93JEcxts
l/1E3rUME2a+F8CQ5FlGP02k66sB5lhzCg81Ym4fxbIP1n09IPmaRzSdM55SpbFy
7OV4VyrJKl7g2Y/utdb17DjYGovu+HX978j1iOH8qUruwAZyWshqdW0CgYEAwIVO
AohxuytN1GlQW4byQvHO4y+AXtZJ4iuBiyOqGhYs8bcnbV3+B0UTHtJyM8Novzj6
OcgiCEHP0Kj6Lj9RYu2sBvsgyfxEURdkHD7DPpYKlheCd7I1a9qk4/UyGx11YdnP
bcqrxv6e2FPBXNZGTXGBmHtIItxHYBEehguRLWUCgYALpR61or7fRYNaMaOAWrGp
OONstpm0nVUNf2LxYa8OW+DVkTRqx7yoBgBmEx2x43kTYyVQp/UgFEcnyDB9V7h7
c0z0W4OU73WSENjrCvY+3a2ghG/tTVRSMNNVK+jjayeTaWB8DsUxMC6bohxPGG7d
qiSsMQ7ajpFhcXv7w6izKQKBgQC+Pz0+vYz+NCXeQRAa0nj29LPIx7kofsRWTz3d
vKmsy7swRhkdN6P/lR/29mnKg1EwnmKP1RjkZfyyKznHl+SaSVoVL/dQAw2TwPS6
AL+6SlU9yw+vrxihc1g8uKICL5M+1hnoWj50EEvyZJoRXuHsR72UbEd1w454/ZHX
TvjxDQKBgCtikMNAqTParY/tX0xNohD7+svTKZt92CxW7Q/17H26ehFKUQvw6Agd
ulR2AVTGi6STEgzXf6UP5CAVhYRw9irCAQYpceL0GVzfZPQsXyLuMCnJ8UD6CBRn
i5vkNY4OZdOuEV9boFOFYa58WRNK7vthHkZJj++Amu3dZ6RHBlLQ
-----END RSA PRIVATE KEY-----',
            'public_key'         => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCasyO1qNW+Z331PzHBQ2sgVuvNKZnpUIzz2F+bhS31q2/b2Aa' .
                                    'XYcU8ljtW2yVcMlUvrvDkRQyynLza2QBfsXTeJpqtKxujqcLXpRN9t81OLjhKebRP/lExq9I6c4xEnwFBx/OqB7' .
                                    'ighDNUZc6zRi80V1K3iloGn12ywpL7vI/+EO+ABXP4sTchwh47bppcBNy4HjOre+NqpLNZkZ02E4lngSaOCY6r3' .
                                    '6TdICaigeQX6n/Xgwm2rRkr0qNIZsd/IoyLYS6/CWUUJjX16qxXt1wwMiwwpRbZ2IULnZ0lI74QXjucD+Ow0OKw' .
                                    'WwgLsN55VUGXVOlpX1GJ2p5mZ3H6YX0B deploy@fixhub',
            'last_run'           => null,
            'build_url'          => 'http://ci.rebelinblue.com/build-status/image/3?branch=master',
            'allow_other_branch' => true,
            'include_dev'        => false,
        ]);
    }
}
