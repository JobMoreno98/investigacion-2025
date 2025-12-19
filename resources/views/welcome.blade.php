{{-- Layout principal (ajusta según tu proyecto, ej: x-app-layout) --}}
@extends('layouts.app')

@section('nav')
    @foreach ($categorias as $item)
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">{{ $item->titulo }}</a>
        </li>
    @endforeach
@endsection
@section('content')
@endsection
