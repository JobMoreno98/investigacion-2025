<flux:navbar>

    @foreach ($enlaces as $link)
        <flux:navbar.item href="{{ route('categorias.show', $link->slug) }}"
            :active="request()->is(ltrim($link->url,  route('categorias.show', $link->slug)))" {{-- {{ url($link->url) }}  --}}>
            {{ $link->titulo }}
        </flux:navbar.item>
    @endforeach

    <flux:spacer />

</flux:navbar>
