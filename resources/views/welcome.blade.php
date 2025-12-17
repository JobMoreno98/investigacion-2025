{{-- Layout principal (ajusta según tu proyecto, ej: x-app-layout) --}}
@extends('layouts.app')

@section('content')
    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Navbar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    @forelse ($categorias as $item)
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">{{ $item->titulo }}</a>
                        </li>
                    @empty
                        <p>Aun no hay nada por registrar</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </nav>
@endsection
