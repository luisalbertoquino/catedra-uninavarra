<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportPledge extends Model
{
    public const TYPES = [
        'inversion' => ['label' => 'Inversión', 'icon' => '💼', 'hint' => 'Quiero invertir capital a cambio de participación o retorno'],
        'donacion' => ['label' => 'Donación', 'icon' => '🎁', 'hint' => 'Quiero aportar recursos sin esperar retorno'],
        'mentoria' => ['label' => 'Mentoría', 'icon' => '🧭', 'hint' => 'Quiero acompañar al equipo con mi experiencia'],
        'alianza' => ['label' => 'Alianza', 'icon' => '🤝', 'hint' => 'Mi empresa u organización quiere trabajar con ellos'],
        'compra' => ['label' => 'Cliente', 'icon' => '🛒', 'hint' => 'Me interesa comprar su producto o servicio'],
    ];

    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'contactado' => 'Contactado',
        'en_proceso' => 'En proceso',
        'concretado' => 'Concretado',
        'descartado' => 'Descartado',
    ];

    protected $fillable = ['user_id', 'venture_id', 'type', 'amount', 'message', 'status', 'admin_notes'];

    protected $casts = ['amount' => 'decimal:2'];

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
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
