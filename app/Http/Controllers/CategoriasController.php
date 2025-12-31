<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerFullView;
use App\Models\Categorias;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categoria = Categorias::with('secciones')->where('id', $id)->first();
        // Verificamos si es la categoría especial
        if ($categoria->titulo == 'Datos Generales') {

            // 1. Buscamos si ya existe un registro
            $datos = AnswerFullView::select('entry_id')
                ->where('user_id', Auth::id()) // Es mas corto usar Auth::id()
                ->where('section_title', 'Datos Generales')
                ->first();

            // 2. Lógica de Decisión: ¿Editar o Crear?
            if ($datos) {
                // SI EXISTE: Redirigir a Editar
                return redirect()->route('answers.edit', $datos->entry_id);
            } else {
                // NO EXISTE: Redirigir a Crear
                // Necesitamos el ID de la sección para enviarlo a la ruta create.
                // Asumimos que la categoría "Datos Generales" tiene una sección hija.
                $seccion = $categoria->secciones->first();

                if ($seccion) {
                    return redirect()->route('answers.show', $seccion->id);
                }
            }
        }
        return view('categorias.index', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorias $categorias)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorias $categorias)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorias $categorias)
    {
        //
    }
}
