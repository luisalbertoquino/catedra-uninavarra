<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportPledge extends Model
{
    /** Formas de apoyo; una intención puede combinar varias. Inversión y donación admiten monto. */
    public const TYPES = [
        'inversion' => ['label' => 'Inversión', 'icon' => 'chart-line', 'hint' => 'Capital a cambio de participación o retorno'],
        'donacion' => ['label' => 'Donación', 'icon' => 'hand-holding-heart', 'hint' => 'Recursos económicos sin esperar retorno'],
        'mentoria' => ['label' => 'Asesorías', 'icon' => 'compass', 'hint' => 'Acompañamiento con tu experiencia y conocimiento'],
        'infraestructura' => ['label' => 'Infraestructura', 'icon' => 'building', 'hint' => 'Espacios, locales, bodegas u oficinas'],
        'materia_prima' => ['label' => 'Materia prima', 'icon' => 'boxes-stacked', 'hint' => 'Insumos para producir'],
        'equipos' => ['label' => 'Equipos', 'icon' => 'gears', 'hint' => 'Maquinaria, tecnología o equipos'],
        'herramientas' => ['label' => 'Herramientas', 'icon' => 'screwdriver-wrench', 'hint' => 'Herramientas de trabajo o software'],
        'suministros' => ['label' => 'Suministros', 'icon' => 'truck-ramp-box', 'hint' => 'Empaques, papelería y otros suministros'],
        'contactos' => ['label' => 'Contactos', 'icon' => 'address-book', 'hint' => 'Conectarlos con clientes, proveedores o aliados'],
        'mercadeo' => ['label' => 'Mercadeo', 'icon' => 'bullhorn', 'hint' => 'Impulsarlos en redes sociales y otros canales'],
        'alianza' => ['label' => 'Alianza', 'icon' => 'handshake', 'hint' => 'Tu empresa u organización quiere trabajar con ellos'],
        'compra' => ['label' => 'Cliente', 'icon' => 'cart-shopping', 'hint' => 'Te interesa comprar su producto o servicio'],
    ];

    /** Tipos que admiten un monto en dinero */
    public const MONETARY = ['inversion', 'donacion'];

    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'contactado' => 'Contactado',
        'en_proceso' => 'En proceso',
        'concretado' => 'Concretado',
        'descartado' => 'Descartado',
    ];

    protected $fillable = ['user_id', 'venture_id', 'type', 'types', 'amount', 'message', 'status', 'admin_notes'];

    protected $casts = ['amount' => 'decimal:2', 'types' => 'array'];

    /** Ordena los tipos según TYPES; el primero queda como tipo principal (`type`). */
    public static function normalizeTypes(array $types): array
    {
        return array_values(array_intersect(array_keys(self::TYPES), $types));
    }

    /** @return list<string> */
    public function typeKeys(): array
    {
        return $this->types ?: [$this->type];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return collect($this->typeKeys())->map(fn ($t) => self::TYPES[$t]['label'] ?? $t)->implode(' · ');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
