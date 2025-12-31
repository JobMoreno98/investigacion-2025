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
                            $savedValue = $existingAnswers[$question->id] ?? null;
                            $defaultValue = $question->options['default_value'] ?? '';
                            $finalValue = old($errorKey, $savedValue ?? $defaultValue);
                        @endphp

                        <div class="mb-4">
                            @php
                                // Mapeo de seguridad: Si el tipo no tiene componente, usa 'text' por defecto
                                $componentName = 'inputs.' . $question->type;
                                if (!view()->exists("components.{$componentName}")) {
                                    $componentName = 'inputs.text';
                                }
                            @endphp
                            <x-dynamic-component :component="$componentName" :question="$question" :value="$finalValue" />
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
