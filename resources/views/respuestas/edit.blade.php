@php
    $titlePage = 'Editar - ' . $seccion->title;
@endphp
<x-layouts.app :title="$titlePage">
    <div class="container m-auto">
        <div class="max-w-7xl mx-auto py-10 px-4">
            @if (session('success'))
                <x-alert type="success">
                    {{ session('success') }}
                </x-alert>
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
                <div class="space-y-5 mt-4  grid grid-cols-1 md:grid-cols-2 gap-4 items-center content-center">

                    <input type="hidden" name="section_ids[]" value="{{ $seccion->id }}">

                    @foreach ($seccion->questions as $question)
                        @php
                            $fieldName = "answers[{$question->id}]";
                            $errorKey = "answers.{$question->id}";
                            $savedValue = $existingAnswers[$question->id] ?? null;
                            $defaultValue = $question->options['default_value'] ?? '';
                            $finalValue = old($errorKey, $savedValue ?? $defaultValue);
                        @endphp


                        @php
                            $isGeneratedCode = ($question->options['code_tag'] ?? '') === 'generated_code';

                            if ($isGeneratedCode) {
                                // ¡ALTO! Es una pregunta especial. Forzamos el componente de sistema.
                                // Asegúrate de que el archivo sea resources/views/components/inputs/system-code.blade.php
                                $componentName = 'inputs.system-code';
                            } else {
                                // 2. LÓGICA ESTÁNDAR
                                // Si no es especial, usamos su tipo de base de datos (text, select, date...)
                                $componentName = 'inputs.' . $question->type;
                            }

                            if (!view()->exists("components.{$componentName}")) {
                                $componentName = 'inputs.text';
                            }
                        @endphp
                        <x-dynamic-component :component="$componentName" :question="$question" :value="$finalValue" />
                    @endforeach

                    <div class="flex justify-center mt-4 col-span-2">
                        <button type="submit"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded shadow-lg transition duration-150">
                            Actualizar
                        </button>
                    </div>
                </div>

            </form>
        </div>
</x-layouts.app>
