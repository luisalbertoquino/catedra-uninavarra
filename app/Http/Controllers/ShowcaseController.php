<?php

namespace App\Http\Controllers;

use App\Models\InvestorProfile;
use App\Models\Portfolio;

/** Páginas informativas: inversionistas invitados al evento y portafolios de cada convocatoria. */
class ShowcaseController extends Controller
{
    public function investors()
    {
        return view('public.investor-profiles', ['profiles' => InvestorProfile::published()->ordered()->get()]);
    }

    public function portfolios()
    {
        return view('public.portfolios', ['portfolios' => Portfolio::published()->ordered()->get()]);
    }
}
