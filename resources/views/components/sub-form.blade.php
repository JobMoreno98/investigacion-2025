@props(['item'])

@php
    $section = $item['subForm']['section'] ?? null;
    $answers = $item['subForm']['answers'] ?? [];
@endphp

@if($section)
    <div class="col-span-2 space-y-5 mt-4 border border-stone-400 rounded p-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50">

        <h4 class="col-span-1 md:col-span-2 text-blue-800 font-bold mb-3 border-b-2 border-blue-500 pb-2">
            {{ $section->title }}
        </h4>

        @foreach ($section->questions as $childQ)

            @php
                $childInputName = "sub_answers[{$item['model']->id}][{$childQ->id}]";

                $childValue = old(
                    "sub_answers.{$item['model']->id}.{$childQ->id}",
                    $answers[$childQ->id] ?? ($childQ->options['default_value'] ?? '')
                );

                $childComponent = 'inputs.' . $childQ->type;

                if (!view()->exists("components.{$childComponent}")) {
                    $childComponent = 'inputs.text';
                }
            @endphp

            <div class="mb-3 w-full">
                <x-dynamic-component
                    :component="$childComponent"
                    :question="$childQ"
                    :value="$childValue"
                    :name="$childInputName"
                />
            </div>

        @endforeach
    </div>
@endif