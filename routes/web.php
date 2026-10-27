<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\JuryController;
use App\Http\Controllers\PledgeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\VentureController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/emprendimientos', [VentureController::class, 'index'])->name('ventures.index');
Route::get('/emprendimientos/{venture}', [VentureController::class, 'show'])->name('ventures.show');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::get('/inversionistas', [InvestorController::class, 'index'])->name('investors');
Route::get('/ediciones', [EditionController::class, 'index'])->name('editions.index');
Route::get('/ediciones/{edition}', [EditionController::class, 'show'])->name('editions.show');

/*
|--------------------------------------------------------------------------
| Usuarios registrados (comunidad e inversionistas)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/mi-cuenta', [AccountController::class, 'index'])->name('dashboard');

    Route::post('/emprendimientos/{venture}/votar', [VoteController::class, 'toggle'])
        ->middleware('throttle:30,1')->name('ventures.vote');
    Route::post('/emprendimientos/{venture}/comentarios', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')->name('ventures.comments.store');
    Route::delete('/comentarios/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/emprendimientos/{venture}/apoyar', [PledgeController::class, 'store'])
        ->middleware('throttle:10,1')->name('ventures.pledge');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel del jurado
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'jury'])->prefix('jurado')->name('jury.')->group(function () {
    Route::get('/', [JuryController::class, 'index'])->name('index');
    Route::get('/evaluar/{venture}', [JuryController::class, 'show'])->name('show');
    Route::post('/evaluar/{venture}', [JuryController::class, 'store'])->middleware('throttle:60,1')->name('store');
});

/*
|--------------------------------------------------------------------------
| Panel de administración
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('ediciones', Admin\EditionController::class)
        ->parameters(['ediciones' => 'edition'])->names('editions')->except('show');
    Route::post('ediciones/{edition}/activar', [Admin\EditionController::class, 'activate'])->name('editions.activate');

    Route::resource('emprendimientos', Admin\VentureController::class)
        ->parameters(['emprendimientos' => 'venture'])->names('ventures')->except('show');
    Route::delete('imagenes/{image}', [Admin\VentureController::class, 'destroyImage'])->name('ventures.images.destroy');

    Route::get('categorias', [Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('categorias', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('categorias/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categorias/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('resultados', [Admin\ResultsController::class, 'index'])->name('results.index');
    Route::put('resultados/{edition}', [Admin\ResultsController::class, 'update'])->name('results.update');
    Route::post('resultados/{edition}/finalistas', [Admin\ResultsController::class, 'pickFinalists'])->name('results.finalists');
    Route::get('resultados/{edition}/exportar', [Admin\ResultsController::class, 'export'])->name('results.export');

    Route::get('comentarios', [Admin\CommentController::class, 'index'])->name('comments.index');
    Route::patch('comentarios/{comment}', [Admin\CommentController::class, 'toggle'])->name('comments.toggle');
    Route::delete('comentarios/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('apoyos', [Admin\PledgeController::class, 'index'])->name('pledges.index');
    Route::get('apoyos/exportar', [Admin\PledgeController::class, 'export'])->name('pledges.export');
    Route::patch('apoyos/{pledge}', [Admin\PledgeController::class, 'update'])->name('pledges.update');

    Route::get('jurados', [Admin\JuryController::class, 'index'])->name('juries.index');
    Route::post('jurados', [Admin\JuryController::class, 'store'])->name('juries.store');
    Route::put('jurados/{user}', [Admin\JuryController::class, 'update'])->name('juries.update');
    Route::delete('jurados/{user}', [Admin\JuryController::class, 'destroy'])->name('juries.destroy');
    Route::get('resultados/{edition}/evaluaciones', [Admin\ResultsController::class, 'evaluations'])->name('results.evaluations');

    Route::get('configuracion', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('configuracion', [Admin\SettingsController::class, 'update'])->name('settings.update');

    Route::get('importar', [Admin\ImportController::class, 'create'])->name('import.create');
    Route::post('importar', [Admin\ImportController::class, 'store'])->name('import.store');

    Route::get('usuarios', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('usuarios/exportar', [Admin\UserController::class, 'export'])->name('users.export');
    Route::patch('usuarios/{user}', [Admin\UserController::class, 'update'])->name('users.update');
});

require __DIR__.'/auth.php';
