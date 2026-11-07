<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Ciclos;
use App\Models\Entry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function generarReporteGeneral()
    {
        $user = Auth::user();

        // Obtenemos el ciclo activo actual
        $ciclo = Ciclos::where('activo', true)->latest()->first();

        if (!$ciclo) {
            return back()->with('error', 'No hay un ciclo activo para generar el reporte.');
        }

        // Consultamos todas las categorías
        $categorias = Categorias::orderBy('sort_order')->get();
        $reportData = [];

        foreach ($categorias as $categoria) {
            $seccionesData = [];

            // Obtenemos las secciones de esta categoría
            foreach ($categoria->secciones()->orderBy('sort_order')->get() as $seccion) {

                // Buscamos los formularios PRINCIPALES del usuario en este ciclo y sección
                $entries = Entry::with(['answers.question', 'children.answers.question', 'children.section'])
                    ->where('user_id', $user->id)
                    ->where('ciclo_id', $ciclo->id)
                    ->where('section_id', $seccion->id)
                    ->whereNull('parent_entry_id') // Solo padres, los hijos vienen en la relación 'children'
                    ->get();

                if ($entries->isNotEmpty()) {
                    $seccionesData[] = [
                        'seccion' => $seccion,
                        'entries' => $entries
                    ];
                }
            }

            // Solo agregamos la categoría al reporte si el usuario tiene datos en ella
            if (!empty($seccionesData)) {
                $reportData[] = [
                    'categoria' => $categoria,
                    'seccionesData' => $seccionesData
                ];
            }
        }

        // Generamos el PDF
        $pdf = Pdf::loadView('reportes.general', compact('reportData', 'user', 'ciclo'));

        // Configuramos para formato Carta y orientación vertical
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Reporte_General_' . $user->name . '.pdf');
    }
    // Agregar dentro de ReportController

    public function generarInformeImpacto()
    {
        $user = Auth::user();
        $ciclo = Ciclos::where('activo', true)->latest()->first();

        if (!$ciclo)
            return back()->with('error', 'No hay un ciclo activo.');

        // 1. Obtener Datos Laborales para el encabezado (Departamento)
        $datosLaborales = Entry::with('answers.question')
            ->where('user_id', $user->id)
            ->whereHas('section', fn($q) => $q->where('title', 'Datos Laborales'))
            ->whereNull('parent_entry_id')->first();

        // 2. Obtener los Proyectos de Investigación
        $proyectos = Entry::with(['answers.question', 'children.answers.question', 'section'])
            ->where('user_id', $user->id)
            ->where('ciclo_id', $ciclo->id)
            ->whereHas('section', fn($q) => $q->where('title', 'Proyectos de Investigación'))
            ->whereNull('parent_entry_id')
            ->get();

        if ($proyectos->isEmpty()) {
            return back()->with('warning', 'No tienes proyectos de investigación registrados en este ciclo.');
        }

        $pdf = Pdf::loadView('reportes.impacto', compact('user', 'ciclo', 'proyectos', 'datosLaborales'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Informe_Impacto_' . $user->name . '.pdf');
    }

    public function generarReportePerfil()
    {
        $user = Auth::user();
        $ciclo = Ciclos::where('activo', true)->latest()->first();
        if (!$ciclo)
            return back()->with('error', 'No hay un ciclo activo.');
        // EN generarReportePerfil:
        $datosGenerales = Entry::with(['answers.question', 'children.answers.question']) // <-- Se agregó children
            ->where('user_id', $user->id)->whereNull('parent_entry_id')
            ->whereHas('section', fn($q) => $q->where('title', 'Datos Generales'))->first();

        // EN generarReportePerfil Y generarInformeImpacto:
        $datosLaborales = Entry::with(['answers.question', 'children.answers.question']) // <-- Se agregó children
            ->where('user_id', $user->id)->whereNull('parent_entry_id')
            ->whereHas('section', fn($q) => $q->where('title', 'Datos Laborales'))->first();

        // 2. Obtener el resto de las categorías/secciones (excluyendo las dos de arriba para no repetirlas)
        $categorias = Categorias::orderBy('sort_order')->get();
        $reportData = [];

        foreach ($categorias as $categoria) {
            $seccionesData = [];
            foreach ($categoria->secciones()->orderBy('sort_order')->get() as $seccion) {
                if (in_array($seccion->title, ['Datos Generales', 'Datos Laborales']))
                    continue;

                $entries = Entry::with(['answers.question', 'children.answers.question', 'children.section'])
                    ->where('user_id', $user->id)->where('ciclo_id', $ciclo->id)
                    ->where('section_id', $seccion->id)->whereNull('parent_entry_id')->get();

                if ($entries->isNotEmpty()) {
                    $seccionesData[] = ['seccion' => $seccion, 'entries' => $entries];
                }
            }
            if (!empty($seccionesData)) {
                $reportData[] = ['categoria' => $categoria, 'seccionesData' => $seccionesData];
            }
        }


        $pdf = Pdf::loadView('reportes.perfil', compact('user', 'ciclo', 'datosGenerales', 'datosLaborales', 'reportData'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Perfil_Academico_' . $user->name . '.pdf');
    }
}