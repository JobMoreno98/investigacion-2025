<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Models\AnswerFullView;
use App\Models\Ciclos;
use App\Models\Entry;
use App\Models\Sections;
use App\Models\Sistemas;
use App\Services\SistemaFilter;
use App\Services\SurveySubmissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    public function __construct(
        protected SurveySubmissionService $surveyService,
        protected SistemaFilter $sistemaFilter,
    ) {
    }

    public function index()
    {
        $proyectos = AnswerFullView::where('user_id', Auth::id())
            ->where('section_id', Sections::idProyectos())
            ->groupBy('entry_id')
            ->orderBy('fecha_creado')
            ->get();

        return view('respuestas.index', compact('proyectos'));
    }

    public function create($id)
    {
        $seccion = $this->seccionDelSistema($id);

        return view('respuestas.create', compact('seccion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request)
    {
        $slug = Sistema::slugActual();
        $sectionId = (int) ($request->section_ids[0] ?? 0);

        // Esta aplicación solo registra secciones de su propio sistema.
        abort_unless(
            Sections::forSistema($slug)->whereKey($sectionId)->exists(),
            403,
            'Esta sección no pertenece a este sistema.'
        );

        $ciclo = Ciclos::forSistema($slug)->vigente()->latest()->first();

        // Un admin puede registrar fuera de la ventana del ciclo: se usa el más reciente del sistema.
        if (! $ciclo && Auth::user()->hasRole('admin')) {
            $ciclo = Ciclos::forSistema($slug)->latest()->first();
        }

        if (! $ciclo) {
            return redirect()->route('dashboard')
                ->with('error', 'Alto. No hay un ciclo activo en este momento o estás fuera de tiempo.');
        }

        $validated = $request->validated();

        $mainEntry = DB::transaction(function () use ($request, $validated, $ciclo, $sectionId) {
            $entry = Entry::create([
                'user_id' => Auth::id(),
                'ciclo_id' => $ciclo->id,
                'section_id' => $sectionId,
            ]);

            $this->surveyService->saveAnswers($entry, $validated, $request);

            return $entry;
        });

        return redirect()->route('answers.edit', $mainEntry->id)
            ->with('success', 'Registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $seccion = $this->seccionDelSistema($id);

        return view('respuestas.create', compact('seccion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $entry = Entry::with('answers.question')->findOrFail($id);

        if ($entry->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        if (! $entry->is_editable) {
            return redirect()->route('dashboard')->with('error', 'Este formulario ya fue enviado.');
        }

        // La sección está en el registro; la primera respuesta solo sirve para registros antiguos.
        $sectionId = $entry->section_id ?? $entry->answers->first()?->question?->section_id;

        if (! $sectionId) {
            return back()->with('error', 'Registro corrupto o vacío');
        }

        // Solo secciones de este sistema; la relación questions() ya viene ordenada por sort_order.
        // Los sub-formularios que no estén disponibles en este sistema los quita SistemaFilter.
        $seccion = Sections::forSistema(Sistemas::slugActual())
            ->with('questions')
            ->findOrFail($sectionId);

        $seccion->setRelation('questions', $this->sistemaFilter->questions($seccion->questions));

        $existingAnswers = $entry->answers->pluck('value_text', 'question_id')->toArray();

        return view('respuestas.edit', compact('entry', 'seccion', 'existingAnswers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSurveyRequest $request, $id)
    {
        $entry = Entry::findOrFail($id);

        if ($entry->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        if (! $entry->is_editable) {
            return redirect()->route('dashboard')->with('error', 'Este formulario ya fue enviado.');
        }

        $validated = $request->validated();

        // Actualización encapsulada en el servicio
        DB::transaction(function () use ($request, $validated, $entry) {
            $this->surveyService->saveAnswers($entry, $validated, $request);
        });

        return redirect()->route('answers.edit', $entry->id)
            ->with('success', 'Actualizado correctamente.');
    }

    public function destroy($id)
    {
        $entry = Entry::findOrFail($id);

        if ($entry->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para eliminar este registro.');
        }

        if (! $entry->is_editable && ! Auth::user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Este formulario ya fue enviado.');
        }

        // Los sub-formularios se borran con el registro (lo hace el evento deleting de Entry).
        $entry->delete();

        return redirect()->route('dashboard')->with('success', 'Formulario eliminado con éxito.');
    }

    public function imprimir($id)
    {
        $entry = Entry::with('answers')->findOrFail($id);

        if ((int) $entry->user_id !== (int) Auth::id() && ! Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para imprimir este registro.');
        }

        $sectionId = $entry->section_id ?? $entry->answers->first()?->question?->section_id;

        abort_unless($sectionId, 404, 'El registro no tiene respuestas.');

        // La relación questions() ya viene ordenada por sort_order.
        $seccion = Sections::with('questions')->findOrFail($sectionId);
        $seccion->setRelation('questions', $this->sistemaFilter->questions($seccion->questions));

        $answersMap = $entry->answers->keyBy('question_id');

        $pdf = Pdf::loadView('entries.pdf', [
            'entry' => $entry,
            'seccion' => $seccion,
            'answersMap' => $answersMap,
        ]);

        return $pdf->stream('formulario_' . $entry->id . '.pdf');
    }

    /**
     * Sección con sus preguntas, solo si pertenece al sistema de esta aplicación
     * y sin los sub-formularios que no están disponibles en él.
     */
    private function seccionDelSistema($id): Sections
    {
        $seccion = Sections::forSistema(Sistemas::slugActual())
            ->with('questions')
            ->findOrFail($id);

        $seccion->setRelation('questions', $this->sistemaFilter->questions($seccion->questions));

        return $seccion;
    }
}