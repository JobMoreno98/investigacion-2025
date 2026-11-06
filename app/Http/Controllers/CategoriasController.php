<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriasController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(Request $request) {}

    public function show(Categorias $categoria)
    {
        if (!isset($categoria->slug) || $categoria->investigacion == true) {
            abort(403, 'El registro no existe.');
        }

        if (in_array($categoria->slug, ['datos-generales', 'datos-laborales'])) {
            $seccion = $categoria->secciones->first();
            

            if ($seccion) {
                $datos = Entry::where('user_id', Auth::id())
                    ->where('section_id', $seccion->id)
                    ->whereNull('parent_entry_id')
                    ->first();


                if ($datos) {
                    return redirect()->route('answers.edit', $datos->id);
                } else {
                    return redirect()->route('answers.show', $seccion->id);
                }
            }
        }

        $categoria->load(['secciones.entries' => function ($query) {
            $query->where('user_id', Auth::id())
                  ->whereNull('parent_entry_id')
                  ->with(['answers.question']); 
        }]);

        return view('categorias.index', compact('categoria'));
    }

    public function edit(Categorias $categorias) {}

    public function update(Request $request, Categorias $categorias) {}

    public function destroy(Categorias $categorias) {}
}