<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte Académico')</title>
    <style>
        /* Estilos Globales Unificados */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        
        /* Tipografía y Encabezados */
        h1.report-title { text-align: center; font-size: 18px; text-transform: uppercase; margin-bottom: 5px; color: #1a4b8c; }
        .subtitle { text-align: center; font-size: 12px; margin-bottom: 20px; color: #555; }
        
        .categoria-header { background-color: #1a4b8c; color: #ffffff; padding: 6px 10px; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; }
        .seccion-header { background-color: #e5e7eb; color: #374151; padding: 5px 10px; font-weight: bold; font-size: 12px; margin-top: 10px; margin-bottom: 5px; border-left: 4px solid #1a4b8c; }
        .sub-entry-title { font-weight: bold; font-size: 11px; color: #1a4b8c; margin-top: 8px; margin-bottom: 4px; padding-left: 10px; }
        
        /* Cajas de datos (usadas en impacto) */
        .header-box { border: 1px solid #1a4b8c; padding: 10px; margin-bottom: 15px; background-color: #f8fafc; }
        .header-box p { margin: 3px 0; }
        
        /* Tablas Estandarizadas */
        table.pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: avoid; }
        table.pdf-table th, table.pdf-table td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        table.pdf-table th { background-color: #f9fafb; font-weight: bold; width: 35%; color: #4b5563; font-size: 10px; text-transform: uppercase; }
        table.pdf-table td { font-size: 11px; color: #111827; }
        
        /* Elementos de UI en PDF */
        .page-break { page-break-after: always; }
        .repeater-badge { display: inline-block; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-bottom: 2px; }
        .empty-state { text-align: center; color: #666; font-style: italic; padding: 10px; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>