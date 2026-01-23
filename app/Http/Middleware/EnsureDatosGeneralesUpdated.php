<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Categorias; // Asegúrate de tener este import
use App\Models\Sections;
use Illuminate\Database\Eloquent\Model;

class EnsureDatosGeneralesUpdated
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Si ya completó los datos, pase directo.
        if ($user->hasUpdatedProfileThisYear()) {
            return $next($request);
        }

        // 2. Rutas globales permitidas
        if ($request->routeIs('dashboard', 'logout')) {
            return $next($request);
        }

        // 3. DETECTAR CATEGORÍA OBJETIVO
        $targetCategoria = null;

        // CASO A: Viendo una categoría (GET)
        if ($request->routeIs('categorias.show')) {
            // Tu dd() confirmó que recibimos el OBJETO directo aquí
            $param = $request->route('categoria');

            if ($param instanceof Categorias) {
                $targetCategoria = $param;
            } elseif ($param instanceof Model) {
                // Por si Laravel devuelve un modelo genérico
                $targetCategoria = $param;
            } elseif (is_numeric($param)) {
                // Por si falla el binding y llega el ID "1"
                $targetCategoria = Categorias::find($param);
            }
        }


        // CASO B: Guardando (POST)
        if ($request->routeIs('answers.store')) {
            $catId = $request->input('categoria_id');
            if ($catId) {
                $targetCategoria = Categorias::find($catId);
            }
        }
        if ($request->routeIs('answers.show')) {
            $seccionId = $request->route('answer');
            $seccion = Sections::find($seccionId);
            //dd($seccion->categoria_id);
            if ($seccion->categoria_id) {
                $targetCategoria = Categorias::find($seccion->categoria_id);
            }
        }

        //dd($targetCategoria);

        // 4. LA COMPARACIÓN FINAL
        if ($targetCategoria) {
            // Limpiamos ambos textos (Mayúsculas y espacios) para que coincidan sí o sí
            $tituloBD = trim(mb_strtolower($targetCategoria->titulo));

            $tituloConstante = trim(mb_strtolower(Categorias::DATOS_GENERALES));

            // Si son iguales, adelante
            if ($tituloBD === $tituloConstante) {
                return $next($request);
            }
        }

        // 5. Bloqueo
        return redirect()
            ->route('dashboard')
            ->with('warning', '⚠️ Acceso restringido: Debes completar "Datos Generales" primero.');
    }
}
