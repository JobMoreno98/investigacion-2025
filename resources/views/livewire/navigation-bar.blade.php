@php
    $profileUpdated = auth()->user()->hasUpdatedProfileThisYear();
@endphp

<flux:navlist.group :heading="__('Platform')">
    @foreach ($enlaces as $link)
        @if ($profileUpdated || $link->isDatosGenerales())
            @php
                $url = route('categorias.show', $link->slug);
                $isActive = request()->url() === $url;
            @endphp

            {{-- Forzamos la altura automática y el padding con "!" --}}
            <flux:navlist.item 
                :href="$url" 
                :active="$isActive"
                wire:navigate 
                x-on:click="$dispatch('close-sidebar')"
                class="relative group !h-auto !py-2.5"
            >
                <span class="absolute top-0 bottom-0 -left-1 w-[2px] bg-indigo-500 rounded-md scale-y-0 group-hover:scale-y-100 transition-transform duration-300 ease-out origin-center"></span>
                <span class="absolute top-0 bottom-0 -right-1 w-[2px] bg-indigo-500 rounded-md scale-y-0 group-hover:scale-y-100 transition-transform duration-300 ease-out origin-center"></span>

                {{-- Cambiamos a div y forzamos el salto de línea con "!whitespace-normal" --}}
                <div class="relative z-10 w-full !whitespace-normal break-words leading-tight">
                    {{ $link->titulo }}
                </div>
            </flux:navlist.item>
        @endif
    @endforeach
</flux:navlist.group>