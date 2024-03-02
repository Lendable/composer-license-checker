<?php

declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(static fn(int $code, string $message): never => throw new \Exception($message));

exec('composer show --locked --format=json symfony/*', output: $output, result_code: $resultCode);

if ($resultCode !== 0) {
    exit($resultCode);
}

/** @var array{locked: list<array{name: non-empty-string, version: non-empty-string, ...}>} $data */
$data = json_decode(implode("\n", $output), true, flags: JSON_THROW_ON_ERROR);

$constraint = $argv[1];
$lowest = $argv[2] === 'lowest';

$names = [];

foreach ($data['locked'] as $installed) {
    if (preg_match('~^v[567]\.~', $installed['version']) === 1) {
        $names[] = $installed['name'];
    }
}

exec('rm composer.lock', result_code: $resultCode);

if ($resultCode !== 0) {
    exit($resultCode);
}

passthru(
    'composer require -W --no-interaction --no-progress --ansi '.($lowest ? '--prefer-lowest --prefer-stable ' : '').implode(' ', array_map(static fn ($name) => $name.':^'.$constraint, $names)),
    $resultCode,
);

exit($resultCode);
