@props(['iosMessage' => null])

<button type="button" data-companion-install hidden {{ $attributes }}>
    {{ $slot->isEmpty() ? __('Install app') : $slot }}
</button>

@if (isset($ios) || $iosMessage)
    <p data-companion-install-ios hidden>
        {{ $ios ?? $iosMessage }}
    </p>
@endif

<script>
(() => {
    const script = document.currentScript;
    const button = script?.previousElementSibling?.matches('[data-companion-install]')
        ? script.previousElementSibling
        : script?.parentElement?.querySelector('[data-companion-install]');
    const iosHint = script?.parentElement?.querySelector('[data-companion-install-ios]');

    if (!button || window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }

    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isStandalone = window.navigator.standalone === true;

    if (iosHint && isIos && !isStandalone) {
        iosHint.hidden = false;
    }

    let promptEvent = null;

    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        promptEvent = event;
        button.hidden = false;
        if (iosHint) {
            iosHint.hidden = true;
        }
    });

    button.addEventListener('click', async () => {
        if (!promptEvent) {
            return;
        }

        promptEvent.prompt();
        await promptEvent.userChoice;
        promptEvent = null;
        button.hidden = true;
    });

    window.addEventListener('appinstalled', () => {
        button.hidden = true;
        promptEvent = null;
        if (iosHint) {
            iosHint.hidden = true;
        }
    });
})();
</script>
