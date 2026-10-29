@props(['title' => null, 'description' => null, 'image' => null])
@php
    $fullTitle = $title ? $title.' · Cátedra UNINAVARRA' : 'Cátedra UNINAVARRA · Emprendimientos que transforman la región';
    $desc = $description ?? 'Conoce, vota y apoya los emprendimientos de los estudiantes Navarristas en Cátedra UNINAVARRA y el LION HUB EVENT.';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#a855f7">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $desc }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ url()->current() }}">
@if ($image)<meta property="og:image" content="{{ $image }}">@endif
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('images/favicon.svg') }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=cabin:400,500,600,700&display=swap" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
