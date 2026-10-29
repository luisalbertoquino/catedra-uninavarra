<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLES = [
        'comunidad' => 'Comunidad',
        'inversionista' => 'Inversionista / Aliado',
        'jurado' => 'Jurado',
        'admin' => 'Administrador',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_expert',
        'show_in_investor_ranking',
        'phone',
        'organization',
        'academic_program',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_expert' => 'boolean',
        'show_in_investor_ranking' => 'boolean',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(SupportPledge::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function isJury(): bool
    {
        return $this->role === 'jurado';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** El administrador principal (webmaster) es el único que puede borrar cuentas. */
    public function isSuperAdmin(): bool
    {
        return $this->isAdmin() && strcasecmp($this->email, (string) config('catedra.superadmin')) === 0;
    }

    public function isInvestor(): bool
    {
        return $this->role === 'inversionista';
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}
