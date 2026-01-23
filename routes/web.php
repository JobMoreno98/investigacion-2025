<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Models\Categorias;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;


use App\Models\Answer;
use Illuminate\Http\Request;


Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');


Route::middleware(['auth', 'datos.generales'])->group(function () {
    Route::resource('answers', AnswerController::class);
    Route::resource('categorias', CategoriasController::class)->parameters([
        'categorias' => 'categoria' // asegura que el parámetro sea {categoria}
    ]);
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    //Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(when(Features::canManageTwoFactorAuthentication() && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'), ['password.confirm'], []))
        ->name('two-factor.show');

    Route::get('/api/validate-folio', function (Request $request) {

        $questionId = $request->input('question_id');
        $baseCode = $request->input('code');
        $entryId = $request->input('entry_id'); // Para excluir el propio registro al editar

        // 1. Si no hay código, retornamos vacío
        if (!$baseCode) return response()->json(['unique_code' => '']);

        // 2. Función recursiva para encontrar el siguiente libre
        $finalCode = $baseCode;
        $counter = 1;

        // Buscamos si existe algun 'Answer' para ESTA pregunta con ESTE valor
        // Excluyendo nuestro propio entry_id (si estamos editando)
        while (Answer::where('question_id', $questionId)
            ->where('value', $finalCode)
            ->when($entryId, fn($q) => $q->where('entry_id', '!=', $entryId))
            ->exists()
        ) {
            $finalCode = $baseCode . '_' . $counter;
            $counter++;
        }

        return response()->json(['unique_code' => $finalCode]);
    })->name('api.validate.folio');
});
