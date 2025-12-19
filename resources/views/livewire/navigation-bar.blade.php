<flux:navbar>

    @foreach ($enlaces as $link)
        <flux:navbar.item href="{{ route('categorias.show', $link->id) }}"
            :active="request()->is(ltrim($link->url,  route('categorias.show', $link->id)))" {{-- {{ url($link->url) }}  --}}>
            {{ $link->titulo }}
        </flux:navbar.item>
    @endforeach

    <flux:spacer />

</flux:navbar>
