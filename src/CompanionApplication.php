<?php

namespace OpenSoutheners\LaravelCompanionApps;

use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\URL;

class CompanionApplication
{
    /**
     * @param  \OpenSoutheners\LaravelCompanionApps\Platform  $platform
     * @param  \OpenSoutheners\LaravelCompanionApps\AppLink|null  $appLink
     * @param  string|null  $storeId App Store's ID (only iOS)
     * @param  string|null  $storeSlug App Store's slug (only iOS)
     * @param  string|null  $storeRegion Application store's region (locale)
     */
    public function __construct(
        protected readonly string $name,
        protected readonly Platform $platform,
        protected ?string $storeId = null,
        protected ?string $storeSlug = null,
        protected ?string $storeRegion = null,
        protected ?AppLink $appLink = null
    ) {
        //
    }

    /**
     * Make new companion application by name and platform.
     */
    public static function make(string $name, Platform $platform): self
    {
        return new self($name, $platform);
    }

    /**
     * Get application's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get application's native platform.
     */
    public function getPlatform(): string
    {
        return $this->platform->name;
    }

    /**
     * Add links scheme to companion application.
     */
    public function linkScheme(string $scheme): self
    {
        $this->appLink = new AppLink($this, $scheme);

        return $this;
    }

    /**
     * Set App Store (iOS only) required id, slug and/or region parameters for the store link.
     */
    public function setStoreOptions(?string $id = null, ?string $slug = null, ?string $region = null): self
    {
        $this->storeId = $id;
        $this->storeSlug = $slug;
        $this->storeRegion = $region;

        return $this;
    }

    /**
     * Check if current companion application have links.
     */
    public function supportsLinks(): bool
    {
        return $this->appLink !== null;
    }

    /**
     * Get app link intent to given path.
     */
    public function link(string $path): ?AppLinkIntent
    {
        if (! $this->supportsLinks() || ! $this->appLink) {
            return null;
        }

        return $this->appLink->getIntent($path);
    }

    /**
     * Get region for the store link.
     */
    private function storeRegion(): string
    {
        /** @var string $storeRegionFallback */
        $storeRegionFallback = config('companion.store.region', app()->getLocale());

        return $this->storeRegion ?? $storeRegionFallback;
    }

    /**
     * Get store slug for the web manifest reference.
     */
    public function getPlatformStore(): string
    {
        return $this->platform->getStore();
    }

    /**
     * Get application store link (Google Play, App Store, etc).
     */
    public function getStoreLink(): string
    {
        return match ($this->getPlatformStore()) {
            'play' => "https://play.google.com/store/apps/details?id={$this->name}",
            'itunes' => "https://apps.apple.com/{$this->storeRegion()}/app/{$this->storeSlug}/id{$this->storeId}",
            default => '',
        };
    }

    /**
     * Get application store HTML meta tag.
     */
    public function getStoreLinkMetaTag(): string
    {
        return match ($this->getPlatformStore()) {
            'itunes' => "<meta name=\"apple-itunes-app\" content=\"app-id={$this->storeId}, app-argument=".URL::current()."\">",
            'play' => "<meta name=\"google-play-app\" content=\"app-id={$this->name}\">",
            default => '',
        };
    }

    /**
     * Get App Store's (Apple) badge image URL.
     */
    private function getAppStoreBadgeImgUrl(): string
    {
        /** @var string $appStoreBadgeUrl */
        $appStoreBadgeUrl = config('companion.store.apple_badge_url', 'https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg');

        return str_replace('{region}', $this->storeRegion(), $appStoreBadgeUrl);
    }

    /**
     * Get application store HTML badge image with link to the store.
     */
    public function getStoreBadgeHtml(int $width = 180, string $alt = ''): string
    {
        $imgExtraAttributes = compact('width', 'alt');
        $imgExtraAttributesStr = '';

        foreach ($imgExtraAttributes as $attribute => $value) {
            $imgExtraAttributesStr .= " {$attribute}=\"{$value}\"";
        }

        return match ($this->getPlatformStore()) {
            'play' => "<a target=\"_blank\" href=\"{$this->getStoreLink()}\"><img src=\"https://play.google.com/intl/en_us/badges/static/images/badges/{$this->storeRegion()}_badge_web_generic.png\"{$imgExtraAttributesStr} /></a>",
            'itunes' => "<a target=\"_blank\" href=\"{$this->getStoreLink()}\"><img src=\"{$this->getAppStoreBadgeImgUrl()}\"{$imgExtraAttributesStr} /></a>",
            default => '',
        };
    }

    /**
     * Get application store link as QR code.
     */
    public function getStoreQrCode(): string
    {
        return (new QRCode())->render($this->getStoreLink());
    }
}
