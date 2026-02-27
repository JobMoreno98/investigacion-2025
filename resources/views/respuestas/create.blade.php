@php
    $titlePage = 'Crear - ' . $seccion->title;
    use App\Helpers\QuestionHelper;
@endphp

<x-layouts.app :title="$titlePage">
    <div class="container m-auto">
        <div class="max-w-7xl mx-auto py-10 px-4">

            {{-- Muestra mensaje de éxito si existe --}}
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

            {{-- IMPORTANTE: enctype es necesario para subir archivos --}}
            <form action="{{ route('answers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="bg-white shadow rounded-lg p-6 border-t-2 border-blue-500">
                    {{-- 1. Iteramos sobre las SECCIONES --}}

                    <input type="hidden" name="section_ids[]" value="{{ $seccion->id }}">

                    <input type="hidden" name="categoria_id" value="{{ $seccion->categoria_id }}">

                    <h3 class="font-semibold text-gray-800">{{ $seccion->title }}</h3>
                    @if ($seccion->description)
                        <p class="text-gray-500 text-sm mb-4">{{ $seccion->description }}</p>
                    @endif

                    <div class="space-y-5 mt-4  grid grid-cols-1 md:grid-cols-2 gap-4 items-center content-center ">
                        @foreach ($seccion->questions as $question)
                            @php
                                $item = QuestionHelper::prepare($question, $existingAnswers);
                                $isSubForm = $item['type'] === 'sub_form';
                            @endphp

                            <div @if ($item['isDependent'] && $item['parentId']) x-data="dependencyComponent({{ $item['parentId'] }}, @js($item['expectedValue']))"
            x-init="init()"
            x-show="show"
            x-cloak @endif
                                class=" w-full mb-4 {{ $isSubForm ? 'col-span-1 md:col-span-2' : '' }} ">

                                @if ($isSubForm)
                                    <x-sub-form :item="$item" />
                                @else
                                    <x-dynamic-component :component="$item['component']" :question="$item['model']" :value="$item['value']" />
                                @endif

                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-center mt-4 col-span-2">
                        <button type="submit"
                            class="bg-blue-600 text-xs hover:bg-blue-700 text-white font-bold py-1 px-4 rounded shadow-lg transition duration-150">
                            Guardar
                        </button>
                    </div>
            </form>
        </div>
</x-layouts.app>

@push('js')
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('dependencyComponent', (parentId, expectedValue) => ({
                show: false,
                parentName: `answers[${parentId}]`,
                expected: expectedValue,

                init() {
                    this.checkDependency();

                    document.addEventListener('change', (e) => {
                        if (e.target.name === this.parentName) {
                            this.checkDependency();
                        }
                    });
                },

                checkDependency() {
                    let parentEls = document.querySelectorAll(`[name='${this.parentName}']`);
                    let val = '';

                    if (parentEls.length > 1) {
                        // radio group
                        let checked = document.querySelector(`[name='${this.parentName}']:checked`);
                        val = checked ? checked.value : '';
                    } else if (parentEls.length === 1) {
                        val = parentEls[0].value;
                    }

                    this.show = (val == this.expected);
                }
            }));

        });
    </script>
@endpush