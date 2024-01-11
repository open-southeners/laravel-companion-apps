<?php

namespace OpenSoutheners\LaravelCompanionApps\Generators;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;

final class AssetLinksGenerator
{
    /**
     * @param array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication> $apps
     * @param array<string, array<string>> $fingerprints
     */
    public function __construct(
        protected readonly array $apps,
        protected readonly array $fingerprints = []
    ) {
        // 
    }

    /**
     * Get asset link for application.
     * 
     * @return array<string, array>
     */
    private function assetLinkFor(CompanionApplication $app): array
    {
        $appName = $app->getName();

        return [
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => $appName,
                'sha256_cert_fingerprints' => $this->fingerprints[$appName],
            ],
        ];
    }

    /**
     * Generate array with file contents structure.
     * 
     * @return array<array<string, array>>
     */
    final public function generate(): array
    {
        $assetLinksArr = [];

        foreach ($this->apps as $app) {
            $assetLinksArr[] = $this->assetLinkFor($app);
        }

        return $assetLinksArr;
    }
}
