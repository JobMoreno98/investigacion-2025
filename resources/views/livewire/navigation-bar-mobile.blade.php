<flux:navlist variant="outline">

    @foreach ($enlaces as $link)
        <flux:navlist.item href="{{ route('categorias.show', $link->id) }}" >
            {{ $link->titulo }}
        </flux:navlist.item>
    @endforeach

</flux:navlist>
