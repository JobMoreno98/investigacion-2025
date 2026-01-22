@props(['label', 'name', 'required' => false])

<div {{ $attributes->merge(['class' => 'mb-2 flex flex-col']) }}>
    <label for="{{ $name }}" class="block font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    {{ $slot }}

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
