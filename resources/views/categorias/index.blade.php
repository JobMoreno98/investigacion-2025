<x-layouts.app :title="$categoria->titulo">
    <div class="container m-auto">
        <h2 class="text-center text-stone-900"> {{ $categoria->titulo }}</h2>
        <p class="text-center text-stone-900">
            {{ $categoria->descripcion }}
        </p>


        @foreach ($categoria->secciones as $key => $value)
            <div class="p-3 my-4 text-stone-900 rounded-sm border-1 border-b-indigo-500">
                {{ $value->title }} <br>
                <x-flux::button size="xs" :href="route('answers.show', $value->id)"> Agregar <flux:icon name="plus" variant="micro" />
                </x-flux::button>
                <div class="p-6">
                    @php
                        // Usamos el accessor que creamos en el modelo
                        $entries = $value->user_entries;
                    @endphp
                    @if ($entries->count() > 0)
                        <ul class="divide-y divide-gray-100">
                            @foreach ($entries as $entry)
                                @php
                                    $titleAnswer = $entry->answers->sortBy(fn($a) => $a->question->sort_order)->first();
                                    $displayTitle = $titleAnswer ? $titleAnswer->value : 'Sin título';
                                @endphp

                                <li
                                    class="py-3 flex justify-between items-center hover:bg-gray-50 transition p-2 rounded">
                                    <div class="flex items-center">
                                        {{-- 
                                        <span
                                            class="bg-blue-100 text-dark-800 text-xs font-semibold mr-1 px-2.5 py-0.5 rounded">
                                            #{{ $entry->id }}
                                        </span>
                                         --}}
                                        <span class="font-medium text-gray-700">
                                            {{ $displayTitle }}
                                        </span>
                                    </div>


                                    <div class="flex space-x-3">
                                        <div class="flex space-x-3">

                                            <a href="{{ route('answers.edit', $entry->id) }}"
                                                class="text-black-600 hover:text-indigo-900 text-sm font-medium">
                                                <flux:icon.printer variant="mini" />
                                            </a>
                                            @if ($entry->is_editable)
                                                {{-- Botones Activos --}}
                                                {{-- Botón Editar --}}
                                                <a href="{{ route('answers.edit', $entry->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    <flux:icon.pencil variant="mini" />
                                                </a>


                                                {{-- Botón Eliminar --}}
                                                <form action="{{ route('answers.destroy', $entry->id) }}" method="POST"
                                                    onsubmit="return confirm('¿Eliminar este registro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                        <flux:icon.trash variant="mini" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>



                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-400 italic text-sm text-center py-4">
                            No has registrado información en esta sección aún.
                        </p>
                    @endif
                </div>

            </div>
        @endforeach
    </div>


</x-layouts.app>
