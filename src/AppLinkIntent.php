<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Illuminate\Contracts\Support\Responsable;

class AppLinkIntent implements Responsable
{
    public function __construct(
        protected readonly AppLink $appLink,
        protected readonly string $path,
        protected ?string $fallbackUrl = null
    ) {
        //
    }

    /**
     * Set fallback URL to app link intent.
     */
    public function fallbackUrl(string $url): self
    {
        $this->fallbackUrl = $url;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return redirect((string) $this);
    }

    /**
     * Convert app link intent to string.
     */
    public function __toString(): string
    {
        $baseIntentUri = "intent://{$this->path}/#Intent;";
        $uriParametersArr = array_filter([
            'scheme' => $this->appLink->scheme,
            'package' => $this->appLink->app->getName(),
            'S.browser_fallback_url' => $this->fallbackUrl,
        ]);

        $uriParameters = '';
        
        foreach ($uriParametersArr as $parameter => $value) {
            $uriParameters .= "{$parameter}={$value};";
        }

        return "{$baseIntentUri}{$uriParameters}end";
    }
}
