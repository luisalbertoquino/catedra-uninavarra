<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Configuración general del sitio (clave → valor), editable desde Admin → Configuración. */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    /** Campos editables, agrupados, con su valor por defecto */
    public const FIELDS = [
        'Portada' => [
            'hero_title' => ['Título principal', "Ideas que *rugen*\ndesde UNINAVARRA\npara el mundo", 'textarea', 'Cada línea va en un renglón. La palabra entre *asteriscos* se resalta en dorado.'],
            'hero_quote' => ['Frase destacada', 'Descubre los emprendimientos creados por los estudiantes Navarristas en «{lema}». Vota por tus favoritos, déjales tu opinión o súmate como inversionista y aliado.', 'textarea', '{lema} se reemplaza por el lema de la edición activa.'],
        ],
        'Gestión de inversiones' => [
            'investment_contact_name' => ['Responsable', 'Centro de Emprendimiento UNINAVARRA', 'text', null],
            'investment_contact_phone' => ['Teléfono / WhatsApp', '+57 322 385 9086', 'text', null],
            'investment_contact_email' => ['Correo', 'centroemprendimiento@uninavarra.edu.co', 'email', null],
        ],
        'Contacto general (pie de página)' => [
            'contact_phone' => ['Teléfono / WhatsApp', '+57 318 071 5286', 'text', null],
            'contact_email' => ['Correo', 'comunicacionesymarketing@uninavarra.edu.co', 'email', null],
            'contact_address' => ['Dirección', 'Calle 10 No. 6-41, Neiva, Huila', 'text', null],
            'contact_site' => ['Sitio web', 'https://uninavarra.edu.co', 'url', null],
            'footer_text' => ['Texto del pie de página', 'Vitrina oficial de los emprendimientos creados por los estudiantes Navarristas en la Cátedra UNINAVARRA. Conócelos, vota por tus favoritos y súmate como inversionista, mentor o aliado.', 'textarea', null],
        ],
    ];

    public static function defaults(): array
    {
        return collect(self::FIELDS)->flatMap(fn ($group) => collect($group)->map(fn ($f) => $f[1]))->all();
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('site-settings', function () {
            try {
                return Schema::hasTable('settings') ? static::pluck('value', 'key')->all() : [];
            } catch (\Throwable) {
                return [];
            }
        });

        return filled($all[$key] ?? null) ? $all[$key] : ($default ?? self::defaults()[$key] ?? null);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site-settings');
    }

    /** Título con saltos de línea y *palabras* resaltadas, escapado para imprimir con {!! !!} */
    public static function headline(string $key): string
    {
        return collect(preg_split('/\r?\n/', trim((string) static::get($key))))
            ->map(fn ($line) => preg_replace('/\*(.+?)\*/', '<span class="text-gradient">$1</span>', e(trim($line))))
            ->implode('<br>');
    }

    /** Número solo con dígitos para enlaces wa.me */
    public static function whatsapp(string $key): string
    {
        $digits = preg_replace('/\D/', '', (string) static::get($key));

        return strlen($digits) === 10 ? '57'.$digits : $digits;
    }
}
