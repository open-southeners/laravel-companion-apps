<?php

namespace OpenSoutheners\LaravelCompanionApps;
use ReflectionEnumUnitCase;

enum Platform
{
    #[PlatformStore('play')]
    case Android;

    #[PlatformStore('itunes')]
    case Apple;

    #[PlatformStore('windows')]
    case Web;

    public function getStore(): string
    {
        $reflection = new ReflectionEnumUnitCase($this, $this->name);

        /**
         * @var array<\ReflectionAttribute<\OpenSoutheners\LaravelCompanionApps\PlatformStore>>
         */
        $reflectionAttributes = $reflection->getAttributes(PlatformStore::class);

        $platformStoreAttribute = reset($reflectionAttributes);

        return $platformStoreAttribute->newInstance()->store;
    }
}
