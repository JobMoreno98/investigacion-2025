@extends('reportes.layout')
@section('title', 'Reporte General - ' . $user->name)

@section('content')
    <h1 class="report-title">Reporte Académico General</h1>
    <div class="subtitle">
        Usuario: <strong>{{ $user->name }}</strong> | Ciclo: <strong>{{ $ciclo->nombre }}</strong>
    </div>

    @foreach ($reportData as $catData)
        <div class="categoria-header">{{ $catData['categoria']->titulo }}</div>

        @foreach ($catData['seccionesData'] as $secData)
            <div class="seccion-header">{{ $secData['seccion']->title }}</div>

            @foreach ($secData['entries'] as $index => $entry)
                @if(count($secData['entries']) > 1)
                    <div class="sub-entry-title">Registro #{{ $index + 1 }}</div>
                @endif

                <table class="pdf-table">
                    <tbody>
                        @foreach ($entry->answers->sortBy(fn($a) => $a->question->sort_order) as $answer)
                            @if ($answer->question->type !== 'sub_form' && $answer->question->type !== 'file')
                                <tr>
                                    <th>{{ $answer->question->label }}</th>
                                    <td>{!! $answer->formatted_value !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                @if ($entry->children->count() > 0)
                    @foreach ($entry->children as $childIndex => $child)
                        <div class="sub-entry-title">&gt; Detalles Adicionales {{ $childIndex + 1 }}</div>
                        <table class="pdf-table">
                            <tbody>
                                @foreach ($child->answers->sortBy(fn($a) => $a->question->sort_order) as $childAnswer)
                                    @if ($childAnswer->question->type !== 'file')
                                    <tr>
                                        <th>{{ $childAnswer->question->label }}</th>
                                        <td>{!! $childAnswer->formatted_value !!}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    @endforeach
@endsection