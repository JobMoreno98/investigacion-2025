<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Categorias; 
use App\Models\Sections;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Model;

class EnsureDatosGeneralesUpdated
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasUpdatedProfileThisYear()) {
            return $next($request);
        }

        if ($request->routeIs('dashboard', 'logout')) {
            return $next($request);
        }
        
        $targetCategoria = null;

        // CASO 1: Viendo la Categoría
        if ($request->routeIs('categorias.show')) {
            $param = $request->route('categoria');
            if ($param instanceof Categorias) {
                $targetCategoria = $param;
            } elseif (is_string($param)) {
                // Si el modelo ya usa el slug en el route binding, esto lo atrapa
                $targetCategoria = Categorias::where('slug', $param)->first() ?? Categorias::find($param);
            }
        }

        // CASO 2: Entrando a Crear un formulario nuevo
        if ($request->routeIs('answers.show', 'answers.create')) {
            $seccionId = $request->route('answer') ?? $request->route('id');
            $seccion = Sections::find($seccionId);
            if ($seccion) {
                $targetCategoria = Categorias::find($seccion->categoria_id);
            }
        }

        // CASO 3: Entrando a Editar un formulario existente (CRÍTICO para ver errores de validación)
        if ($request->routeIs('answers.edit')) {
            $entryId = $request->route('answer') ?? $request->route('id') ?? $request->route('proyecto');
            $entry = Entry::find($entryId);
            if ($entry && $entry->section_id) {
                $seccion = Sections::find($entry->section_id);
                if ($seccion) {
                    $targetCategoria = Categorias::find($seccion->categoria_id);
                }
            }
        }

        // CASO 4: Acciones de Guardar (POST) o Actualizar (PUT/PATCH)
        // Ambas envían el input oculto 'categoria_id' desde tu vista Blade
        if ($request->routeIs('answers.store', 'answers.update')) {
            $catId = $request->input('categoria_id');
            if ($catId) {
                $targetCategoria = Categorias::find($catId);
            }
        }

        // LA COMPARACIÓN FINAL (Usando el slug)
        if ($targetCategoria && $targetCategoria->slug === 'datos-generales') {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with('warning', 'Acceso restringido: Debes completar "Datos Generales" primero.');
    }
}