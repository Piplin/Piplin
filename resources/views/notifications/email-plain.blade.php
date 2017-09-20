<?php

if (!empty($greeting)) {
    echo $greeting;
} else {
    echo $level == 'error' ? trans('emails.whoops') : trans('emails.welcome', ['name' => $name]);
}

echo "\n\n";

if (!empty($introLines)) {
    echo implode("\n", $introLines) . "\n\n";
}

if (isset($actionText)) {
    echo "{$actionText}: {$actionUrl}\n\n";
}

if (!empty($outroLines)) {
    echo implode("\n", $outroLines) . "\n\n";
}

echo trans('emails.regards') . "\n" . config('app.name') . "\n";
