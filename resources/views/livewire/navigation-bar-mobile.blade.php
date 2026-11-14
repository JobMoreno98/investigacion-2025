@php
    $profileUpdated = auth()->user()->hasUpdatedProfileThisYear();
@endphp

{{-- 1. Eliminamos class="grid" de aquí --}}
<flux:navlist.group :heading="__('Platform')">
    @foreach ($enlaces as $link)
        @if ($profileUpdated || $link->isDatosGenerales())
            @php
                $url = route('categorias.show', $link->slug);
                $isActive = request()->url() === $url;
            @endphp

            {{-- Añadimos h-auto y py-2 para que el botón pueda crecer verticalmente --}}
            <flux:navlist.item 
                :href="$url" 
                :active="$isActive"
                wire:navigate 
                x-on:click="$dispatch('close-sidebar')"
                class="relative group h-auto py-2"
            >
                <span class="absolute top-0 bottom-0 -left-1 w-[2px] bg-indigo-500 rounded-md scale-y-0 group-hover:scale-y-100 transition-transform duration-300 ease-out origin-center"></span>
                <span class="absolute top-0 bottom-0 -right-1 w-[2px] bg-indigo-500 rounded-md scale-y-0 group-hover:scale-y-100 transition-transform duration-300 ease-out origin-center"></span>

                {{-- Quitamos truncate y agregamos whitespace-normal y leading-snug (interlineado) --}}
                <span class="relative z-10 block w-full min-w-0 whitespace-normal break-words leading-snug">
                    {{ $link->titulo }}
                </span>
            </flux:navlist.item>
        @endif
    @endforeach
</flux:navlist.group>