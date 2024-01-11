<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class PlatformStore
{
    public function __construct(public string $store)
    {
        // 
    }
}
