<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    /** URL pública de un archivo guardado en el disco "public" o de una URL externa. */
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function store(?UploadedFile $file, string $folder, ?string $previous = null): ?string
    {
        if (! $file) {
            return $previous;
        }
        static::delete($previous);

        return $file->store($folder, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function youtubeId(?string $url): ?string
    {
        return $url && preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{11})~', $url, $m) ? $m[1] : null;
    }

    /** Convierte enlaces de YouTube, Vimeo o Google Drive a URL embebible. */
    public static function videoEmbed(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if ($id = static::youtubeId($url)) {
            return "https://www.youtube-nocookie.com/embed/{$id}?rel=0";
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }
        if (preg_match('~drive\.google\.com/file/d/([\w-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        return null;
    }
}
