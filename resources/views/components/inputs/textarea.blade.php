@props(['question', 'value'])

@php
    $name = "answers[{$question->id}]";
    $errorKey = "answers.{$question->id}";
@endphp

<x-inputs.wrapper class="col-span-1 md:col-span-2" :label="$question->label" :name="$errorKey" :required="$question->is_required">
    <textarea name="{{ $name }}" id="{{ $errorKey }}"
        class="w-full form-input rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        {{ $attributes }}>{{ $value }}</textarea>
</x-inputs.wrapper>
