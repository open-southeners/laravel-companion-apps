<?php

namespace OpenSoutheners\LaravelCompanionApps;

class AppLink
{
    public function __construct(
        public readonly CompanionApplication $app,
        public readonly string $scheme
    ) {
        //
    }

    public function getIntent(string $path): AppLinkIntent
    {
        return new AppLinkIntent($this, $path);
    }
}
