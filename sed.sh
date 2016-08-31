#!/bin/sh

#sed -i 's/CachetHQ\\Tests\\Cachet/Gitamin\\Tests/' `grep CachetHQ -rl .`
sed -i 's/Lang::get/trans/' `grep 'Lang::get' -rl ./app`
