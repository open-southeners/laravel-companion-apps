<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Jenssegers\Agent\Facades\Agent;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

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
         * @param \OpenSoutheners\LaravelCompanionApps\CompanionApplication|string $app
         * @param string $path
         * @param string|null $fallbackTo
         * @return \Illuminate\Http\RedirectResponse
         */
        return function (CompanionApplication|string $app, string $path, ?string $fallbackTo = null) {
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
