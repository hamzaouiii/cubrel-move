<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <!-- Favicon for multiple platforms -->
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="shortcut icon" href="/favicon.ico">
        <meta name="theme-color" content="#ffffff">
        <meta name="description" content="Automatisierte Angebote und Rechnungen für kleine Unternehmen in der Region Regensburg. Einfache Systemlösung für Ihre Prozesse.">
        <meta property="og:title" content="Automatisierung Regensburg">
        <meta property="og:description" content="Wiederkehrende Aufgaben automatisieren – Fixpreis ab 400 €.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://automatisierung-regensburg.de/">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!-- Scripts -->
        @vite(['resources/js/app.js'])

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
