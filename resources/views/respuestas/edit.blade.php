@php
    $titlePage = 'Editar - ' . $seccion->title;
@endphp
<x-layouts.app :title="$titlePage">
    <div class="container m-auto">
        <div class="max-w-3xl mx-auto py-10 px-4">
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

            <form action="{{ route('answers.update', $entry->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white shadow rounded-lg p-6 border-t-2 border-blue-500">

                    <input type="hidden" name="section_ids[]" value="{{ $seccion->id }}">

                    @foreach ($seccion->questions as $question)
                        @php
                            $fieldName = "answers[{$question->id}]";
                            $errorKey = "answers.{$question->id}";
                            $dbValue = $existingAnswers[$question->id] ?? null;
                        @endphp

                        <div class="mb-4">
                            <label class="font-medium text-gray-700 mb-1">
                                {{ $question->label }}
                                @if ($question->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @switch($question->type)
                                @case('number')
                                    <input type="number" name="{{ $fieldName }}" value="{{ old($errorKey, $dbValue) }}"
                                        step="any"
                                        class="text-stone-900 border-gray-300 rounded-xs shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2">
                                @break

                                @case('text')
                                    <input type="text" name="{{ $fieldName }}" value="{{ old($errorKey, $dbValue) }}"
                                        class="border-gray-300 text-stone-900 rounded-xs shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2">
                                @break

                                @case('textarea')
                                    <textarea name="{{ $fieldName }}"
                                        class="text-stone-900 border-gray-300 rounded-xs shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2">{{ old($errorKey, $dbValue) }}</textarea>
                                @break

                                @case('select')
                                    <select name="{{ $fieldName }}"
                                        class="form-select text-stone-900 border-gray-300 rounded-xs shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2">
                                        <option value="">Seleccione...</option>

                                        {{-- Verifica que exista y sea iterable --}}
                                        @if (!empty($question->options['choices']))
                                            @foreach ($question->options['choices'] as $choice)
                                                {{-- Ahora accedemos como array: $choice['value'] y $choice['label'] --}}
                                                <option value="{{ $choice['value'] }}"
                                                    {{ old($errorKey, $dbValue) == $choice['value'] ? 'selected' : '' }}>
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
                                            class="form-select text-stone-900 border-gray-900 rounded-sm shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2"
                                            @if ($enableSearch) placeholder="Escribe para buscar..." @endif>
                                            <option value="">Seleccione una opción...</option>

                                            @foreach ($options as $id => $label)
                                                {{-- Nota: Comparamos como string para evitar fallos de '1' vs 1 --}}
                                                <option value="{{ $id }}"
                                                    {{ (string) old($errorKey, $dbValue) === (string) $id ? 'selected' : '' }}>
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

                                @case('date')
                                    @php $opts = $question->options ?? []; @endphp
                                    <input type="date" name="{{ $fieldName }}" value="{{ old($errorKey, $dbValue) }}"
                                        min="{{ $opts['min_date'] ?? '' }}" max="{{ $opts['max_date'] ?? '' }}"
                                        class="form-input text-stone-900 border-gray-300 rounded-xs shadow-md focus:border-blue-500 focus:ring focus:ring-blue-200 w-full p-2">
                                @break

                                @case('file')
                                    {{-- Mostrar enlace al archivo actual si existe --}}
                                    @if ($dbValue)
                                        <div class="text-sm text-gray-600 mb-2">
                                            Archivo actual: <a href="{{ asset('storage/' . $dbValue) }}" target="_blank"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xs file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">Ver
                                                archivo</a>
                                        </div>
                                    @endif

                                    <input type="file" name="{{ $fieldName }}"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xs file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="text-xs text-gray-500">Deja esto vacío para mantener el archivo actual.</p>
                                @break
                            @endswitch

                            @error($errorKey)
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                    <div class="flex justify-center mt-4">
                        <button type="submit"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded shadow-lg transition duration-150">
                            Actualizar
                        </button>
                    </div>
                </div>

            </form>
        </div>
</x-layouts.app>
