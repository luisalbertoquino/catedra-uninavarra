<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'catedra:crear-admin {email : Correo del administrador}
        {--nombre=Administrador Cátedra : Nombre para mostrar}
        {--enviar-enlace : No pide contraseña: envía al correo un enlace para que la persona cree la suya}
        {--reemplaza= : Correo de un administrador anterior que se elimina al terminar}';

    protected $description = 'Crea (o actualiza) una cuenta de administrador con contraseña segura';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        $old = $this->option('reemplaza') ? User::where('email', strtolower($this->option('reemplaza')))->first() : null;
        if ($this->option('reemplaza') && (! $old || ! $old->isAdmin() || $old->email === $email)) {
            $this->error('El correo de --reemplaza no es de otro administrador.');

            return self::FAILURE;
        }

        if ($this->option('enviar-enlace')) {
            $password = Str::password(24);
        } else {
            $password = $this->secret('Contraseña (mínimo 12 caracteres, letras y números)');

            $v = Validator::make(['email' => $email, 'password' => $password], [
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(12)->letters()->numbers()],
            ]);
            if ($v->fails()) {
                foreach ($v->errors()->all() as $e) {
                    $this->error($e);
                }

                return self::FAILURE;
            }
            if ($this->secret('Repite la contraseña') !== $password) {
                $this->error('Las contraseñas no coinciden.');

                return self::FAILURE;
            }
        }

        $user = User::updateOrCreate(['email' => $email], [
            'name' => $this->option('nombre'), 'role' => 'admin', 'password' => Hash::make($password),
        ]);
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        $this->info("Administrador listo: {$email}");

        if ($this->option('enviar-enlace')) {
            $status = PasswordBroker::sendResetLink(['email' => $email]);
            $status === PasswordBroker::RESET_LINK_SENT
                ? $this->info('Enlace para crear la contraseña enviado a '.$email.' (vence en '.config('auth.passwords.users.expire').' minutos).')
                : $this->warn('No se pudo enviar el enlace ('.__($status).'). Puede usar «¿La olvidaste?» en el inicio de sesión.');
        }

        if ($old) {
            $old->delete();
            $this->info("Administrador anterior eliminado: {$old->email}");
        }

        return self::SUCCESS;
    }
}
