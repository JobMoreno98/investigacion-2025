<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSurveyRequest;

use App\Models\Entry;
use App\Models\Sections;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $seccion = Sections::with('questions')->where('id', $id)->first();
        return view('respuestas.create', compact('seccion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request)
    {
        $validated = $request->validated();

        // 1. Crear el Entry (El contenedor del envío)
        $entry = Entry::create([
            'user_id' => auth()->id(), // o null si es anónimo
        ]);

        // 2. Guardar las respuestas vinculadas a ese Entry
        foreach ($validated['answers'] as $questionId => $value) {
            // Lógica de archivos (igual que antes)
            if ($request->hasFile("answers.{$questionId}")) {
                $value = $request->file("answers.{$questionId}")->store('uploads', 'public');
            }

            // Usamos la relación para crear (asumiendo que definiste hasMany en Entry)
            $entry->answers()->create([
                'question_id' => $questionId,
                'value' => $value,
            ]);
        }

        return back()->with('success', 'Enviado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $seccion = Sections::with('questions')->where('id', $id)->get();
        return view('respuestas.create', compact('seccion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Answer $answer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Answer $answer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $answer)
    {
        //
    }
}
