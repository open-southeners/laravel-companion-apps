<?php

namespace OpenSoutheners\LaravelCompanionApps\Generators;

use Illuminate\Support\Collection;
use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

class ManifestGenerator
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(protected array $options = [])
    {
        $this->options = array_merge($this->manifestConfig(), $this->options);
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
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        $content = $this->manifestFields($this->options);

        $content['prefer_related_applications'] = $this->options['prefer_related_applications'] ?? false;

        if ($this->options['related_applications'] ?? false) {
            $content['related_applications'] = Collection::make(Companion::list())->map(function (CompanionApplication $app) {
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

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    protected function manifestFields(array $manifest): array
    {
        if (($manifest['enabled'] ?? true) === false) {
            return [];
        }

        $fields = [
            'name',
            'short_name',
            'description',
            'id',
            'start_url',
            'scope',
            'display',
            'orientation',
            'theme_color',
            'background_color',
            'lang',
            'dir',
            'categories',
            'icons',
            'shortcuts',
            'screenshots',
            'share_target',
            'protocol_handlers',
        ];

        $content = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $manifest)) {
                continue;
            }

            $value = $manifest[$field];

            if ($value === null || $value === []) {
                continue;
            }

            $content[$field] = $value;
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    protected function manifestConfig(): array
    {
        /** @var array<string, mixed> $pwa */
        $pwa = config('companion.pwa', []);
        /** @var array<string, mixed> $manifest */
        $manifest = $pwa['manifest'] ?? [];

        $manifest = array_filter($manifest, static fn ($value): bool => $value !== null);

        return array_merge([
            'enabled' => $pwa['enabled'] ?? true,
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'description' => null,
            'id' => null,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => null,
            'theme_color' => null,
            'background_color' => null,
            'lang' => null,
            'dir' => null,
            'categories' => [],
            'icons' => [],
            'shortcuts' => [],
            'screenshots' => [],
            'share_target' => null,
            'protocol_handlers' => [],
            'related_applications' => true,
            'prefer_related_applications' => false,
        ], $this->legacyManifestConfig($pwa), $manifest);
    }

    /**
     * @param  array<string, mixed>  $pwa
     * @return array<string, mixed>
     */
    protected function legacyManifestConfig(array $pwa): array
    {
        return array_filter([
            'name' => $pwa['name'] ?? null,
            'short_name' => $pwa['short_name'] ?? null,
            'description' => $pwa['description'] ?? null,
            'start_url' => $pwa['start_url'] ?? null,
            'scope' => $pwa['scope'] ?? null,
            'display' => $pwa['display'] ?? null,
            'orientation' => $pwa['orientation'] ?? null,
            'theme_color' => $pwa['theme_color'] ?? null,
            'background_color' => $pwa['background_color'] ?? null,
            'icons' => $pwa['icons'] ?? null,
            'shortcuts' => $pwa['shortcuts'] ?? null,
            'screenshots' => $pwa['screenshots'] ?? null,
            'share_target' => $pwa['share_target'] ?? null,
            'protocol_handlers' => $pwa['protocol_handlers'] ?? null,
            'related_applications' => $pwa['related_applications'] ?? null,
            'prefer_related_applications' => $pwa['prefer_related_applications'] ?? null,
        ], static fn ($value): bool => $value !== null);
    }
}
