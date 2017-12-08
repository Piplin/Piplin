<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'default' => [

        // By default, images are presented at 80px by 80px if no size parameter is supplied.
        // You may request a specific image size, which will be dynamically delivered from Gravatar
        // by passing a single pixel dimension (since the images are square):
        'size'   => 160,

        // the fallback image, can be a string or a url
        // for more info, visit: http://en.gravatar.com/site/implement/images/#default-image
        'fallback' => 'identicon',

        // would you like to return a https://... image
        'secure' => (substr(env('APP_URL'), 0, 5) === 'https'),

        // Gravatar allows users to self-rate their images so that they can indicate if an image
        // is appropriate for a certain audience. By default, only 'G' rated images are displayed
        // unless you indicate that you would like to see higher ratings.
        // Available options:
        // g: suitable for display on all websites with any audience type.
        // pg: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.
        // r: may contain such things as harsh profanity, intense violence, nudity, or hard drug use.
        // x: may contain hardcore sexual imagery or extremely disturbing violence.
        'maximumRating' => 'pg',

        // If for some reason you wanted to force the default image to always load, you can do that setting this to true
        'forceDefault' => false,

        // If you require a file-type extension (some places do) then you may also add an (optional) .jpg
        // extension to that URL
        'forceExtension' => 'jpg',
    ],
];
