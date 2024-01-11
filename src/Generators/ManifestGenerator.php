<?php

namespace OpenSoutheners\LaravelCompanionApps\Generators;

use OpenSoutheners\LaravelCompanionApps\Companion;
use OpenSoutheners\LaravelCompanionApps\Manager;
use OpenSoutheners\LaravelCompanionApps\Platform;

class ManifestGenerator
{
    public function __construct(protected array $options = [])
    {
        $this->options = array_merge($this->options, [
            'related_applications' => true,
        ]);
    }

    public function relatedApplications(bool $value = true): self
    {
        $this->options['related_applications'] = $value;

        return $this;
    }

    public function preferRelatedApplications(bool $value = true): self
    {
        $this->options['prefer_related_applications'] = $value;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function generate(): array
    {
        $content = [];

        $content['prefer_related_applications'] = $this->options['prefer_related_applications'] ?? false;

        if ($this->options['related_applications'] ?? false) {
            $content['related_applications'] = collect(Manager::apps())->map(function (Companion $app) {
                $appData = [
                    'platform' => $app->getPlatformStore(),
                    'url' => $app->getStoreLink(),
                ];

                if ($app->getPlatform() === Platform::Android->name) {
                    $appData['id'] = $app->getName();
                }

                return $appData;
            })->toArray();
        }

        return $content;
    }
}
