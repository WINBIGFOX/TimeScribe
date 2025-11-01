<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia></title>

    <!-- Scripts -->
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased select-none cursor-default {{ PHP_OS_FAMILY === 'Windows' ? 'windows' : '' }}">
@inertia

@if (config('services.google.analytics.id'))
    <script async
            src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics.id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', '{{ config('services.google.analytics.id') }}', {
            'user_id': '{{ \Native\Desktop\Facades\Settings::get('id', '')  }}',
            'user_properties': {
                'app_version': '{{ config('nativephp.version') }}',
                'locale': '{{ app()->getLocale() }}',
            }
        });
    </script>
@endif
</body>
</html>
