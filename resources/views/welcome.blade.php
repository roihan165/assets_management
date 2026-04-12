<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Welcome - {{ config('app.name') }}</title>

    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="min-h-screen">

    <div 
        class="min-h-screen bg-cover bg-center"
        style="background-image: url('{{ asset('images/sinergi.jpeg') }}');"
    >
        <livewire:navbar />
        <livewire:hero />
    </div>

    @livewireScripts
</body>
</html>