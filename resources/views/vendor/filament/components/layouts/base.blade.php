<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @livewireStyles
    @filamentStyles
</head>
<body class="filament-body">
{{ $slot }}

@livewireScripts
@filamentScripts
<livewire:notifications />
</body>
</html>
