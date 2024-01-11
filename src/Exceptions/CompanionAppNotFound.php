<?php

namespace OpenSoutheners\LaravelCompanionApps\Exceptions;

use OpenSoutheners\LaravelCompanionApps\Platform;
use RuntimeException;

class CompanionAppNotFound extends RuntimeException
{
    public static function forPlatform(string $app, Platform $platform)
    {
        return new self("'{$app}' not found in registered list of {$platform->name} companion apps");
    }
}
