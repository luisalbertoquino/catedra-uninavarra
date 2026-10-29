<?php

namespace App\Notifications;

use App\Models\Edition;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Correo de bienvenida para jurados y administradores, con un enlace para crear (o cambiar) su contraseña.
 * El enlace vence en DAYS días y deja de servir apenas la persona guarda una contraseña.
 */
class WelcomeInvitation extends Notification
{
    public const DAYS = 7;

    public function __construct(public ?string $note = null)
    {
    }

    public function via(User $user): array
    {
        return ['mail'];
    }

    public static function url(User $user): string
    {
        return URL::temporarySignedRoute('invitation.show', now()->addDays(self::DAYS), [
            'user' => $user->id, 'hash' => self::hash($user),
        ]);
    }

    /** Huella de la contraseña actual: al cambiarla, los enlaces anteriores quedan inválidos. */
    public static function hash(User $user): string
    {
        return substr(hash_hmac('sha256', (string) $user->password, config('app.key')), 0, 32);
    }

    public function toMail(User $user): MailMessage
    {
        $edition = Edition::current();
        $mail = (new MailMessage)
            ->subject('Bienvenida a '.config('app.name'))
            ->greeting('¡Hola, '.strtok($user->name, ' ').'!');

        // Texto editable en Admin → Configuración; el diseño del correo no cambia
        $text = strtr((string) Setting::get($user->isJury() ? 'welcome_jury' : 'welcome_admin'), [
            '{nombre}' => $user->name,
            '{rol}' => $user->isJury() ? ($user->is_expert ? ($edition?->expert_title ?? 'profesor experto') : 'jurado') : 'administrador/a',
            '{evento}' => $edition?->name ?? config('app.name'),
        ]);
        foreach (preg_split('/\r?\n/', $text) as $paragraph) {
            if (trim($paragraph) !== '') {
                $mail->line(trim($paragraph));
            }
        }

        if ($this->note) {
            $mail->line($this->note);
        }

        return $mail
            ->line('Tu usuario es **'.$user->email.'**. Para entrar, crea tu contraseña con este botón:')
            ->action('Crear mi contraseña', self::url($user))
            ->line('El enlace vence en '.self::DAYS.' días y solo se puede usar una vez. Si se vence, pide uno nuevo con «¿La olvidaste?» en '.route('login').'.')
            ->salutation("Un saludo,\n".config('app.name'));
    }
}
