<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Jenssegers\Agent\Facades\Agent;

/**
 * @mixin \Illuminate\Routing\Redirector
 */
class Redirector
{
    public function toApp()
    {
        /**
         * Redirect to companion app's internal link.
         * 
         * @param \OpenSoutheners\LaravelCompanionApps\Companion|string $app
         * @param string $path
         * @param string|null $fallbackTo
         * @return \Illuminate\Http\RedirectResponse
         */
        return function (Companion|string $app, string $path, ?string $fallbackTo = null) {
            if (is_string($app)) {
                return match (true) {
                    Agent::isAndroidOS() => $this->toApp(Companion::android($app), $path, $fallbackTo),
                    Agent::isiOS() => $this->toApp(Companion::apple($app), $path, $fallbackTo),
                };
            }
            
            $linkIntent = $app->link($path);

            if ($fallbackTo) {
                $linkIntent->fallbackUrl($fallbackTo);
            }

            return $this->to((string) $linkIntent);
        };
    }
}
