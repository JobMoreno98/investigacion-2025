@props(['class' => 'h-12 w-auto'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}>
    <x-app-logo-icon class="{{ $class }}" />
</div>