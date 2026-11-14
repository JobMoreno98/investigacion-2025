@props([
    'question',
    'value' => '',
    // Nombre del campo del formulario (p. ej. answers[12] o sub_answers[5][12]). Lo normal es que lo pase el padre.
    'name' => null,
    // Dueño del registro que se está llenando/editando. Si no se pasa, el usuario logueado.
    // (Un admin que edita el registro de otro usuario debe pasar el user_id del dueño.)
    'ownerId' => null,
    // ref_entry_id guardado de la respuesta (solo alcance "user"); selecciona la opción exacta al editar.
    'selectedEntryId' => null,
])

@php
    // Si el padre no manda el nombre, se usa el de una pregunta de primer nivel: answers[ID].
    // Dentro de un sub-formulario el padre SÍ debe pasarlo (sub_answers[ID_PADRE][ID]).
    $name = $name ?: "answers[{$question->id}]";

    /** @var \App\Services\EntryReader $reader */
    $reader = app(\App\Services\EntryReader::class);

    // options.scope:  "user" = solo los registros del usuario  |  "all" = respuestas de todos los usuarios
    $isUserScope = $reader->referenceScope($question) === \App\Services\EntryReader::SCOPE_USER;

    // user: [entry_id => texto]    all: [texto => texto]
    $options = $reader->referenceOptions($question, (int) ($ownerId ?? auth()->id()));

    $isEmpty = empty($options);

    // Opción que debe aparecer seleccionada:
    //  1) el ref_entry_id guardado, 2) el valor enviado (id tras un error de validación, o texto en "all"),
    //  3) el texto guardado (registros anteriores a ref_entry_id).
    $selectedKey = null;

    if ($isUserScope && $selectedEntryId !== null && array_key_exists((int) $selectedEntryId, $options)) {
        $selectedKey = (int) $selectedEntryId;
    } elseif (is_scalar($value) && $value !== '') {
        if (array_key_exists((string) $value, $options)) {
            $selectedKey = (string) $value;
        } elseif (($found = array_search((string) $value, $options, true)) !== false) {
            $selectedKey = $found;
        }
    }

    $placeholder = $isEmpty
        ? ($isUserScope ? 'Aún no tienes registros en la sección de origen' : 'Todavía no hay respuestas registradas')
        : 'Selecciona una opción...';

    $scopeHint = $isUserScope
        ? 'Se muestran solo tus registros.'
        : 'Se muestran las respuestas de todos los usuarios.';
@endphp

<x-inputs.wrapper :label="$question->label" :name="$name" :required="$question->is_required" :helperText="$question->helper_text"
    :class="$attributes->get('class')">

    <select id="{{ $name }}" name="{{ $name }}"
        class="form-select w-full rounded-md border border-stone-300 p-2 text-stone-900 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
        @disabled($isEmpty)>
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $key => $text)
            <option value="{{ $key }}" @selected($selectedKey !== null && (string) $key === (string) $selectedKey)>
                {{ $text }}
            </option>
        @endforeach
    </select>

</x-inputs.wrapper>