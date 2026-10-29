<?php

namespace App\Notifications;

use App\Models\Edition;
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
        $event = Edition::current()?->name ?? config('app.name');
        $mail = (new MailMessage)
            ->subject('Bienvenida a '.config('app.name'))
            ->greeting('¡Hola, '.strtok($user->name, ' ').'!');

        if ($user->isJury()) {
            $mail->line("Te damos la bienvenida como **".($user->is_expert ? (Edition::current()?->expert_title ?? 'profesor experto') : 'jurado')."** de {$event}.")
                ->line('En el panel del jurado verás los emprendimientos asignados con su video pitch, y los calificarás con la rúbrica oficial. Tus notas se guardan solas y puedes ajustarlas hasta que cierre la evaluación.');
        } else {
            $mail->line("Te damos la bienvenida como **administrador/a** de la plataforma de {$event}.")
                ->line('Desde el panel podrás gestionar ediciones, emprendimientos, jurados, resultados, comentarios y apoyos de inversionistas.');
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
