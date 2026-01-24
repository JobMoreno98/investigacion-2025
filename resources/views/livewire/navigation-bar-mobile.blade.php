@php
    $profileUpdated = auth()->user()->hasUpdatedProfileThisYear();
@endphp

<flux:navlist.group :heading="__('Platform')" class="grid">
    @foreach ($enlaces as $link)
        @if ($profileUpdated || $link->isDatosGenerales())
            <flux:navlist.item :href="route('categorias.show', $link->id)" wire:navigate
                x-on:click="$dispatch('close-sidebar')" class="
        h-auto lg:h-11 rounded-lg relative group
    ">
                {{-- Cambiamos rounded-full por rounded-md --}}
                <span
                    class="
        absolute top-0 bottom-0 -left-1 w-[2px] bg-indigo-500 rounded-md
        scale-y-0 group-hover:scale-y-100 
        transition-transform duration-300 ease-out origin-center
    "></span>

                {{-- Cambiamos rounded-full por rounded-md --}}
                <span
                    class="
        absolute top-0 bottom-0 -right-1 w-[2px] bg-indigo-500 rounded-md
        scale-y-0 group-hover:scale-y-100 
        transition-transform duration-300 ease-out origin-center
    "></span>

                <span class="block whitespace-normal break-words relative z-10">
                    {{ $link->titulo }}
                </span>
            </flux:navlist.item>
        @endif
    @endforeach
</flux:navlist.group>
