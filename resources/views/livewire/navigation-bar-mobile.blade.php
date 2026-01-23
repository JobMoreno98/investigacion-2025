@php
    $profileUpdated = auth()->user()->hasUpdatedProfileThisYear();
@endphp

<flux:navlist.group :heading="__('Platform')" class="grid">
    @foreach ($enlaces as $link)
        @if ($profileUpdated || $link->isDatosGenerales())
            <flux:navlist.item :href="route('categorias.show', $link->id)" wire:navigate
                x-on:click="$dispatch('close-sidebar')">
                <span class="block whitespace-normal break-words">
                    {{ $link->titulo }}
                </span>
            </flux:navlist.item>
        @endif
    @endforeach
</flux:navlist.group>
