<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @companionPwaTags
</head>
<body>
    <main>
        <h1>{{ __('You are offline') }}</h1>
        <p>{{ __('Please check your connection and try again.') }}</p>
    </main>
</body>
</html>
