@props(['question', 'value', 'name' => null])

@php
    // 1. CONFIGURACIÓN
    // Si el valor viene de la BD, suele ser un array o JSON decodificado.
    // Si es null, inicializamos vacío.
    $currentScore = $value['score'] ?? null;
    $currentText  = $value['text'] ?? null;

    // Nombres para los inputs HTML (para que funcionen con old() y POST normal)
    // answers[55][score] y answers[55][text]
    $baseName = $name ?? "answers[{$question->id}]";
    $nameScore = "{$baseName}[score]";
    $nameText  = "{$baseName}[text]";
    
    // Wire models: asumiendo que tu wire:model principal es "answers.55"
    // Livewire permite el enlace profundo automáticamente.
    $wireModelBase = $attributes->wire('model')->value(); // ej: answers.55
    $wireScore = $wireModelBase ? "{$wireModelBase}.score" : null;
    $wireText  = $wireModelBase ? "{$wireModelBase}.text" : null;

    // Opciones del admin
    $min = $question->options['min_score'] ?? 0;
    $max = $question->options['max_score'] ?? 10;
@endphp

<x-inputs.wrapper 
    :label="$question->label" 
    :name="$baseName" 
    :required="$question->is_required" 
    :helper-text="$question->helper_text"
>
    <div class="flex flex-col md:flex-row gap-3">
        
        {{-- COLUMNA 1: PUNTUACIÓN --}}
        <div class="w-full md:w-1/4">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                Puntuación ({{ $min }}-{{ $max }})
            </label>
            <input 
                type="number" 
                name="{{ $nameScore }}"
                min="{{ $min }}"
                max="{{ $max }}"
                step="1"
                placeholder="#"
                class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center font-bold text-lg"
                
                {{-- Vinculación Livewire --}}
                @if($wireScore) wire:model.blur="{{ $wireScore }}" @endif
                
                {{-- Recuperar valor old si falla validación --}}
                value="{{ old(str_replace(['[',']'], ['.',''], $nameScore), $currentScore) }}"
            >
        </div>

        {{-- COLUMNA 2: JUSTIFICACIÓN --}}
        <div class="w-full md:w-3/4">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                Justificación
            </label>
            <input 
                type="text" 
                name="{{ $nameText }}"
                placeholder="Justifica esta calificación..."
                class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                
                {{-- Vinculación Livewire --}}
                @if($wireText) wire:model.blur="{{ $wireText }}" @endif

                {{-- Recuperar valor old --}}
                value="{{ old(str_replace(['[',']'], ['.',''], $nameText), $currentText) }}"
            >
        </div>

    </div>
    
    {{-- Mostrar errores específicos de los sub-campos --}}
    @error($wireScore) <span class="text-red-500 text-xs block mt-1">Error en puntos: {{ $message }}</span> @enderror
    @error($wireText) <span class="text-red-500 text-xs block mt-1">Error en texto: {{ $message }}</span> @enderror

</x-inputs.wrapper>