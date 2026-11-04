<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestorProfile;
use App\Support\Media;
use Illuminate\Http\Request;

/** Perfiles de los inversionistas invitados que se muestran en el sitio (página Inversionistas e inicio). */
class InvestorProfileController extends Controller
{
    public function index()
    {
        return view('admin.investor-profiles.index', ['profiles' => InvestorProfile::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.investor-profiles.form', ['profile' => new InvestorProfile(['is_published' => true, 'sort' => InvestorProfile::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        InvestorProfile::create($this->validated($request));

        return redirect()->route('admin.investor-profiles.index')->with('status', 'Inversionista agregado.');
    }

    public function edit(InvestorProfile $profile)
    {
        return view('admin.investor-profiles.form', compact('profile'));
    }

    public function update(Request $request, InvestorProfile $profile)
    {
        $profile->update($this->validated($request, $profile));

        return redirect()->route('admin.investor-profiles.index')->with('status', 'Inversionista actualizado.');
    }

    public function destroy(InvestorProfile $profile)
    {
        Media::delete($profile->photo);
        $profile->delete();

        return back()->with('status', 'Inversionista eliminado.');
    }

    private function validated(Request $request, ?InvestorProfile $profile = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'expertise' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'sort' => ['nullable', 'integer', 'between:0,999'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ], [], ['headline' => 'cargo', 'bio' => 'trayectoria', 'expertise' => 'temas', 'photo' => 'foto', 'sort' => 'orden']);

        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_published'] = $request->boolean('is_published');
        if ($request->boolean('remove_photo') && ! $request->hasFile('photo')) {
            Media::delete($profile?->photo);
            $data['photo'] = null;
        } else {
            $data['photo'] = Media::store($request->file('photo'), 'investors', $profile?->photo);
        }

        return $data;
    }
}
