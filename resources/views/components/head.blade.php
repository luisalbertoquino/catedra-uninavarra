@props(['title' => null, 'description' => null, 'image' => null])
@php
    $fullTitle = $title ? $title.' · Cátedra UNINAVARRA' : 'Cátedra UNINAVARRA · Emprendimientos que transforman la región';
    $desc = $description ?? 'Conoce, vota y apoya los emprendimientos de los estudiantes Navarristas en la Cátedra UNINAVARRA y el LION HUB EVENT.';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#070a1a">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $desc }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ url()->current() }}">
@if ($image)<meta property="og:image" content="{{ $image }}">@endif
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%23f5b70a'/><text x='50' y='70' font-size='60' text-anchor='middle' font-family='Arial' font-weight='bold' fill='%23070a1a'>U</text></svg>">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|outfit:300,400,500,600&display=swap" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
