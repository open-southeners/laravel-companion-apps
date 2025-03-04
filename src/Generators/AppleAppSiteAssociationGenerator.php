<?php

namespace OpenSoutheners\LaravelCompanionApps\Generators;

/**
 * Utility to generate Apple App Site Association (AASA) file content.
 *
 * @see https://developer.apple.com/documentation/xcode/supporting-associated-domains
 */
final class AppleAppSiteAssociationGenerator
{
    /**
     * @param array<string, array<string, string>> $apps
     */
    public function __construct(
        protected readonly array $apps
    ) {
        //
    }

    /**
     * Get app links part to the site association.
     */
    private function getAppLinks(): array
    {
        $appLinksArr = [
            'apps' => array_keys($this->apps),
            'details' => [],
        ];

        foreach ($this->apps as $appID => $paths) {
            $appLinksArr['details'][] = compact('appID', 'paths');
        }

        return ['applinks' => $appLinksArr];
    }

    final public function generate(): array
    {
        // TODO: activitycontinuation?
        // TODO: webcredentials?
        return $this->getAppLinks();
    }
}
