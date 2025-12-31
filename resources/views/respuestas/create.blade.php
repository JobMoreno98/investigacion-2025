@php
    $titlePage = 'Crear - ' . $seccion[0]->title;
@endphp
<x-layouts.app :title="$titlePage">
    <div class="container m-auto">
        <div class="max-w-3xl mx-auto py-10 px-4">

            {{-- Muestra mensaje de éxito si existe --}}
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    {{ session('success') }}
                </div>
            @endif
            {{--  
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
--}}
            {{-- IMPORTANTE: enctype es necesario para subir archivos --}}
            <form action="{{ route('answers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="bg-white shadow rounded-lg p-6 border-t-2 border-blue-500">
                    {{-- 1. Iteramos sobre las SECCIONES --}}
                    @foreach ($seccion as $section)
                        <input type="hidden" name="section_ids[]" value="{{ $section->id }}">

                        <h3 class="font-semibold text-gray-800">{{ $section->title }}</h3>
                        @if ($section->description)
                            <p class="text-gray-500 text-sm mb-4">{{ $section->description }}</p>
                        @endif

                        <div class="space-y-5 mt-4">
                            {{-- 2. Iteramos sobre las PREGUNTAS de esa sección --}}
                            @foreach ($section->questions as $question)
                                {{-- Variables auxiliares para limpiar el código --}}
                                @php
                                    $fieldName = "answers[{$question->id}]"; // Nombre para el HTML
                                    $errorKey = "answers.{$question->id}"; // Nombre para buscar errores
                                    $oldValue = old($errorKey); // Valor anterior (si falló validación)
                                @endphp

                                <div class="flex flex-col">
                                    <label class="font-medium text-gray-700 mb-1">
                                        {{ $question->label }}
                                        @if ($question->is_required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    {{-- 3. SWITCH para renderizar el input correcto --}}
                                    @switch($question->type)
                                        {{-- TEXTO CORTO --}}
                                        @case('text')
                                            <input type="text" name="{{ $fieldName }}" value="{{ $oldValue }}"
                                                class="border-gray-300 text-stone-900 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full">
                                        @break

                                        {{-- TEXTO LARGO --}}
                                        @case('textarea')
                                            <textarea name="{{ $fieldName }}" rows="3"
                                                class="text-stone-900 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full">{{ $oldValue }}</textarea>
                                        @break

                                        {{-- NÚMERO --}}
                                        @case('number')
                                            <input type="number" name="{{ $fieldName }}" value="{{ $oldValue }}"
                                                step="any"
                                                class="text-stone-900 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full">
                                        @break

                                        {{-- FECHA (Con min/max dinámicos) --}}
                                        @case('date')
                                            @php
                                                // Recuperamos las opciones de forma segura. Si es null, usamos un array vacío.
                                                $opts = $question->options ?? [];
                                            @endphp

                                            <input type="date" name="{{ $fieldName }}" value="{{ $oldValue }}"
                                                min="{{ $opts['min_date'] ?? '' }}" max="{{ $opts['max_date'] ?? '' }}"
                                                class="text-stone-900 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full">
                                        @break

                                        {{-- SELECT / DROPDOWN --}}
                                        @case('select')
                                            <select name="{{ $fieldName }}" class="form-select w-full ...">
                                                <option value="">Seleccione...</option>

                                                {{-- Verifica que exista y sea iterable --}}
                                                @if (!empty($question->options['choices']))
                                                    @foreach ($question->options['choices'] as $choice)
                                                        {{-- Ahora accedemos como array: $choice['value'] y $choice['label'] --}}
                                                        <option value="{{ $choice['value'] }}"
                                                            {{ $oldValue == $choice['value'] ? 'selected' : '' }}>
                                                            {{ $choice['label'] }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @break

                                        @case('catalog')
                                            @php
                                                // 1. Identificar qué catálogo se configuró
                                                $catalogName = $question->options['catalog_name'] ?? '';

                                                // 2. Obtener los datos usando tu Helper Universal
                                                $options = \App\Helpers\CatalogProvider::get($catalogName);

                                                // 3. Decidir si activamos el buscador (si son muchos items)
                                                $enableSearch = count($options) > 10;
                                            @endphp

                                            <div wire:ignore> {{-- Importante si usas Livewire para que no resetee el plugin --}}
                                                <select name="{{ $fieldName }}" id="select-{{ $question->id }}"
                                                    class="form-select w-full"
                                                    @if ($enableSearch) placeholder="Escribe para buscar..." @endif>
                                                    <option value="">Seleccione una opción...</option>

                                                    @foreach ($options as $id => $label)
                                                        {{-- Nota: Comparamos como string para evitar fallos de '1' vs 1 --}}
                                                        <option value="{{ $id }}"
                                                            {{ (string) $oldValue === (string) $id ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Script para activar el buscador (TomSelect) --}}
                                            @if ($enableSearch)
                                                <script>
                                                    // Asegúrate de tener cargada la librería TomSelect en tu layout
                                                    new TomSelect("#select-{{ $question->id }}", {
                                                        create: false,
                                                        sortField: {
                                                            field: "text",
                                                            direction: "asc"
                                                        }
                                                    });
                                                </script>
                                            @endif
                                        @break

                                        {{-- ARCHIVO --}}
                                        @case('file')
                                            @php
                                                // Lógica de presentación:
                                                // Convertimos "pdf,jpg" (formato validación) a ".pdf,.jpg" (formato HTML accept)
                                                $acceptAttribute = '';

                                                if (!empty($question->options['allowed_formats'])) {
                                                    $formats = explode(',', $question->options['allowed_formats']);
                                                    // Trim quita espacios, y agregamos el punto
                                                    $formatted = array_map(fn($ext) => '.' . trim($ext), $formats);
                                                    $acceptAttribute = implode(',', $formatted);
                                                }
                                            @endphp

                                            <input type="file" name="{{ $fieldName }}" accept="{{ $acceptAttribute }}"
                                                {{-- Aquí va el atributo mágico --}}
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                                            {{-- Ayuda visual para el usuario --}}
                                            @if ($acceptAttribute)
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Formatos aceptados: {{ $question->options['allowed_formats'] }}
                                                </p>
                                            @endif
                                        @break

                                        @case('sub_form')
                                            @php
                                                // 1. Identificamos qué sección incrustar
                                                $targetSectionId = $question->options['target_section_id'];

                                                // 2. Buscamos las preguntas de esa sección (Mejor si las pasas desde el controlador para optimizar)
                                                $childSection = \App\Models\Sections::with('questions')->find(
                                                    $targetSectionId,
                                                );

                                                // 3. Obtenemos si ya hay un Entry guardado (El valor de la respuesta es el ID del entry hijo)
                                                $childEntryId = $existingAnswers[$question->id] ?? null;

                                                // 4. Si hay entry hijo, cargamos sus respuestas
                                                $childAnswers = [];
                                                if ($childEntryId) {
                                                    $childEntry = \App\Models\Entry::with('answers')->find(
                                                        $childEntryId,
                                                    );
                                                    // Mapeamos [question_id => value]
                                                    $childAnswers = $childEntry->answers
                                                        ->pluck('value', 'question_id')
                                                        ->toArray();
                                                }
                                            @endphp

                                            @if ($childSection)
                                                <div class="border-l-4 border-blue-500 pl-4 ml-2 my-4 bg-gray-50 p-4 rounded">
                                                    <h4 class="text-blue-800 font-bold mb-3">{{ $childSection->title }}
                                                        </h4>

                                                    {{-- Iteramos las preguntas de la sección HIJA --}}
                                                    @foreach ($childSection->questions as $childQ)
                                                        @php
                                                            // NAMING CRÍTICO: sub_answers[PADRE][HIJO]
                                                            $childInputName = "sub_answers[{$question->id}][{$childQ->id}]";

                                                            // Recuperamos valor: old > guardado > default
                                                            $childValue = old(
                                                                "sub_answers.{$question->id}.{$childQ->id}",
                                                                $childAnswers[$childQ->id] ?? '',
                                                            );
                                                        @endphp

                                                        <div class="mb-3">
                                                            <label
                                                                class="block text-sm text-gray-600">{{ $childQ->label }}</label>

                                                            {{-- Renderizado simplificado (copia tu switch grande aquí) --}}
                                                            @if ($childQ->type === 'text')
                                                                <input type="text" name="{{ $childInputName }}"
                                                                    value="{{ $childValue }}" class="form-input w-full">
                                                            @elseif($childQ->type === 'select')
                                                                {{-- ... tu lógica de select ... --}}
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @break
                                    @endswitch



                                    {{-- 4. MOSTRAR ERRORES DE VALIDACIÓN --}}
                                    @error($errorKey)
                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="flex justify-center mt-4">
                        <button type="submit"
                            class="bg-blue-600 text-xs hover:bg-blue-700 text-white font-bold py-1 px-4 rounded shadow-lg transition duration-150">
                            Guardar
                        </button>
                    </div>
            </form>
        </div>
</x-layouts.app>
