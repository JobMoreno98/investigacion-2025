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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- IMPORTANTE: enctype es necesario para subir archivos --}}
            <form action="{{ route('answers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                {{-- 1. Iteramos sobre las SECCIONES --}}
                @foreach ($seccion as $section)
                    <input type="hidden" name="section_ids[]" value="{{ $section->id }}">
                    <div class="bg-white shadow rounded-lg p-6 border-t-4 border-blue-500">
                        <h2 class="text-xl font-semibold text-gray-800">{{ $section->title }}</h2>
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
                                            <select name="{{ $fieldName }}"
                                                class="text-stone-900 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full">
                                                <option value="">Seleccione una opción...</option>

                                                @if ($question->options)
                                                    @foreach ($question->options as $key => $label)
                                                        <option value="{{ $key }}"
                                                            {{ $oldValue == $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @break

                                        {{-- ARCHIVO --}}
                                        @case('file')
                                            <input type="file" name="{{ $fieldName }}"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        @break
                                    @endswitch

                                    {{-- 4. MOSTRAR ERRORES DE VALIDACIÓN --}}
                                    @error($errorKey)
                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-lg transition duration-150">
                        Enviar Respuestas
                    </button>
                </div>
            </form>
        </div>
</x-layouts.app>
