<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Notifications\WelcomeInvitation;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['groups' => Setting::FIELDS]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (Setting::FIELDS as $fields) {
            foreach ($fields as $key => [$label, $default, $type]) {
                $rules[$key] = match ($type) {
                    'email' => ['nullable', 'email', 'max:150'],
                    'url' => ['nullable', 'url', 'max:255'],
                    'textarea' => ['nullable', 'string', 'max:1000'],
                    default => ['nullable', 'string', 'max:200'],
                };
            }
        }

        Setting::put($request->validate($rules));

        return back()->with('status', 'Configuración guardada.');
    }

    /** Vista previa del correo de bienvenida tal como le llega a un jurado o a un administrador. */
    public function welcomePreview(Request $request)
    {
        $user = $request->user()->replicate()->forceFill([
            'id' => $request->user()->id,
            'role' => $request->tipo === 'jurado' ? 'jurado' : 'admin',
            'is_expert' => false,
        ]);

        return (new WelcomeInvitation)->toMail($user)->render();
    }
}
