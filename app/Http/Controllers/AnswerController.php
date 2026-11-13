<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Models\AnswerFullView;
use App\Models\Ciclos;
use App\Models\Entry;
use App\Models\Questions;
use App\Models\Sections;
use App\Services\SurveySubmissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class AnswerController extends Controller
{
    protected SurveySubmissionService $surveyService;

    // Inyección de dependencias del nuevo servicio
    public function __construct(SurveySubmissionService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function index()
    {
        $proyectos = AnswerFullView::where('user_id', Auth::id())
            ->where('section_title', 'Proyectos de Investigación')
            ->groupBy('entry_id')
            ->orderBy('fecha_creado')
            ->get();

        return view('respuestas.index', compact('proyectos'));
    }

    public function create($id)
    {
        $seccion = Sections::with('questions')->where('id', $id)->firstOrFail();
        return view('respuestas.create', compact('seccion'));
    }
    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request)
    {
        $fecha = date('Y-m-d');

        $ciclo = Ciclos::whereJsonContains('sistemas', 'sia')
            ->where('activo', true)
            ->whereDate('inicio', '<=', $fecha)
            ->whereDate('fin', '>=', $fecha)
            ->latest()
            ->first();

        if (!isset($ciclo->id) || !Auth::user()->hasRole('admin')) {
            return redirect()->route('dashboard')
                ->with('error', 'Alto. No hay un ciclo activo en este momento o estás fuera de tiempo.');
        }

        $validated = $request->validated();

        $mainEntry = DB::transaction(function () use ($request, $validated, $ciclo) {
            $entry = Entry::create([
                'user_id' => Auth::id(),
                'ciclo_id' => $ciclo->id,
                'section_id' => $request->section_ids[0] 
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

        $seccion = Sections::with('questions')->where('id', $id)->first();

        return view('respuestas.create', compact('seccion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $entry = Entry::with(['answers.question', 'answers'])->findOrFail($id);

        if ($entry->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        if (!$entry->is_editable) {
            return redirect()->route('dashboard')->with('error', 'Este formulario ya fue enviado.');
        }

        $firstAnswer = $entry->answers->first();
        $sectionId = $firstAnswer ? $firstAnswer->question->section_id : null;

        if (!$sectionId) {
            return back()->with('error', 'Registro corrupto o vacío');
        }

        $seccionesProhibidas = Sections::where('investigacion', true)->pluck('id')->all();

        $seccion = Sections::where('investigacion', false)
            ->with([
                'questions' => fn($q) => $q
                    ->where(
                        fn($q) => $q
                            ->where('type', '!=', 'sub_form')
                            ->orWhereNotIn('options->target_section_id', $seccionesProhibidas)
                    )
                    ->orderBy('sort_order')
            ])
            ->findOrFail($sectionId);
        $existingAnswers = $entry->answers->pluck('value_text', 'question_id')->toArray();

        return view('respuestas.edit', compact('entry', 'seccion', 'existingAnswers'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSurveyRequest $request, $id)
    {
        $entry = Entry::findOrFail($id);

        if ($entry->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        if (!$entry->is_editable) {
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

        if ($entry->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para eliminar este registro.');
        }

        if (!$entry->is_editable && !Auth::user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Este formulario ya fue enviado.');
        }

        $entry->delete();

        return redirect()->route('dashboard')->with('success', 'Formulario eliminado con éxito.');
    }
    public function imprimir($id)
    {
        // 1. Cargamos los datos exactamente igual que en tu vista web
        $entry = Entry::with('answers')->findOrFail($id);

        if ($entry->user_id !== Auth::user()->id) {
            abort(403, 'No tienes permiso para imprimir este registro.');
        }
        $idSecion = ($entry->answers->first()->question->section_id);

        $seccion = \App\Models\Sections::with([
            'questions' => function ($q) {
                $q->orderBy('sort_order'); // Asegurar orden correcto
            }
        ])->where('id', $idSecion)->orderBy('sort_order')->first();

        $answersMap = $entry->answers->keyBy('question_id');

        // 2. Generamos el PDF apuntando a una vista nueva y exclusiva para impresión
        $pdf = Pdf::loadView('entries.pdf', [
            'entry' => $entry,
            'seccion' => $seccion,
            'answersMap' => $answersMap,
        ]);

        // 3. Devolvemos el archivo para que el navegador lo descargue
        return $pdf->stream('formulario_' . $entry->id . '.pdf');
    }
}
