<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'catedra:crear-admin {email : Correo del administrador} {--nombre=Administrador Cátedra : Nombre para mostrar}';

    protected $description = 'Crea (o actualiza) una cuenta de administrador con contraseña segura';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
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

        $user = User::updateOrCreate(['email' => $email], [
            'name' => $this->option('nombre'), 'role' => 'admin', 'password' => Hash::make($password),
        ]);
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        $this->info("Administrador listo: {$email}");

        return self::SUCCESS;
    }
}
