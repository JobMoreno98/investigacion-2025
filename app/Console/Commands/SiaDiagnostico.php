<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * php artisan sia:diagnostico
 *
 * Revisa la configuración compartida por todos los sistemas y avisa de lo que haría que algo
 * "desaparezca" o falle sin dar error. Pensado para correrse en el núcleo tras cada cambio grande
 * (y en CI/cron). Termina con código distinto de cero si encuentra errores.
 */
class SiaDiagnostico extends Command
{
    protected $signature = 'sia:diagnostico';

    protected $description = 'Revisa la consistencia de sistemas, secciones, preguntas y registros';

    private int $errores = 0;

    private int $avisos = 0;

    public function handle(): int
    {
        $slugs = DB::table('sistemas')->pluck('slug', 'id');
        $secciones = DB::table('sections')->whereNull('deleted_at')->pluck('title', 'id');
        $categorias = DB::table('categorias')->pluck('titulo', 'id');
        $preguntas = DB::table('questions')->whereNull('deleted_at')
            ->get(['id', 'label', 'type', 'section_id', 'options']);

        $sistemasDeSeccion = $this->pivot('section_sistema', 'section_id');
        $sistemasDeCategoria = $this->pivot('categoria_sistema', 'categoria_id');
        $sistemasDeCiclo = $this->pivot('ciclo_sistema', 'ciclo_id');

        // 1. Cosas sin sistema: no aparecen en ninguna aplicación
        $this->revisar('error', 'Secciones sin ningún sistema', $secciones
            ->filter(fn ($titulo, $id) => empty($sistemasDeSeccion[$id]))
            ->map(fn ($titulo, $id) => "#{$id} {$titulo}"));

        $this->revisar('error', 'Categorías sin ningún sistema', $categorias
            ->filter(fn ($titulo, $id) => empty($sistemasDeCategoria[$id]))
            ->map(fn ($titulo, $id) => "#{$id} {$titulo}"));

        $this->revisar('error', 'Ciclos sin ningún sistema', DB::table('ciclos')->get(['id', 'inicio', 'fin'])
            ->filter(fn ($ciclo) => empty($sistemasDeCiclo[$ciclo->id]))
            ->map(fn ($ciclo) => "#{$ciclo->id} ({$ciclo->inicio} → {$ciclo->fin})"));

        // 2. Sub-formularios
        $destinoInexistente = collect();
        $noEnTodos = collect();

        foreach ($preguntas->where('type', 'sub_form') as $pregunta) {
            $opciones = json_decode((string) $pregunta->options, true) ?: [];
            $destino = (int) ($opciones['target_section_id'] ?? 0);

            if (! $destino || ! isset($secciones[$destino])) {
                $destinoInexistente->push("pregunta #{$pregunta->id} «{$pregunta->label}» (sección #{$pregunta->section_id})");
                continue;
            }

            $faltan = array_diff($sistemasDeSeccion[$pregunta->section_id] ?? [], $sistemasDeSeccion[$destino] ?? []);

            if ($faltan) {
                $nombres = collect($faltan)->map(fn ($id) => $slugs[$id] ?? $id)->implode(', ');
                $noEnTodos->push("pregunta #{$pregunta->id} «{$pregunta->label}»: no se mostrará en [{$nombres}] porque «{$secciones[$destino]}» no está asignada ahí");
            }
        }

        $this->revisar('error', 'Sub-formularios cuya sección destino no existe o no está configurada', $destinoInexistente);
        $this->revisar('aviso', 'Sub-formularios que no estarán disponibles en todos los sistemas de su sección (puede ser intencional)', $noEnTodos);

        // 3. Referencias
        $preguntasPorId = $preguntas->keyBy('id');
        $referenciasRotas = collect();

        foreach ($preguntas->where('type', 'reference') as $pregunta) {
            $opciones = json_decode((string) $pregunta->options, true) ?: [];
            $origen = $preguntasPorId->get((int) ($opciones['source_question_id'] ?? 0));

            if (! $origen) {
                $referenciasRotas->push("pregunta #{$pregunta->id} «{$pregunta->label}»: la pregunta de origen no existe");
            } elseif (isset($opciones['source_section_id']) && (int) $opciones['source_section_id'] !== (int) $origen->section_id) {
                $referenciasRotas->push("pregunta #{$pregunta->id} «{$pregunta->label}»: la pregunta de origen no pertenece a la sección de origen configurada");
            }
        }

        $this->revisar('error', 'Referencias con origen roto', $referenciasRotas);

        // 4. Dependencias
        $dependenciasRotas = collect();

        foreach ($preguntas as $pregunta) {
            $opciones = json_decode((string) $pregunta->options, true) ?: [];

            if (! filter_var($opciones['is_dependent'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $padre = $preguntasPorId->get((int) ($opciones['depends_on_question_id'] ?? 0));

            if (! $padre || (int) $padre->section_id !== (int) $pregunta->section_id) {
                $dependenciasRotas->push("pregunta #{$pregunta->id} «{$pregunta->label}»: depende de una pregunta inexistente o de otra sección");
            }
        }

        $this->revisar('error', 'Preguntas con dependencia rota (nunca se mostrarían)', $dependenciasRotas);

        // 5. Registros
        $sinSeccion = DB::table('entries')->whereNull('section_id')->whereNull('deleted_at')->count();
        $this->revisar('aviso', 'Registros sin section_id (anteriores a la columna)', $sinSeccion ? collect(["{$sinSeccion} registros"]) : collect());

        $huerfanos = DB::table('entries as hijo')
            ->join('entries as padre', 'padre.id', '=', 'hijo.parent_entry_id')
            ->whereNull('hijo.deleted_at')->whereNotNull('padre.deleted_at')
            ->pluck('hijo.id')
            ->map(fn ($id) => "registro hijo #{$id}");

        $this->revisar('error', 'Sub-formularios activos cuyo registro padre fue borrado', $huerfanos);

        $this->newLine();
        $this->line("Errores: {$this->errores} · Avisos: {$this->avisos}");

        return $this->errores > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<int, array<int>>  id del elemento => ids de sus sistemas */
    private function pivot(string $tabla, string $llave): array
    {
        return DB::table($tabla)->get()
            ->groupBy($llave)
            ->map(fn (Collection $filas) => $filas->pluck('sistema_id')->all())
            ->all();
    }

    private function revisar(string $nivel, string $titulo, Collection $lineas): void
    {
        if ($lineas->isEmpty()) {
            $this->info("✔ {$titulo}");

            return;
        }

        $nivel === 'error' ? $this->errores += $lineas->count() : $this->avisos += $lineas->count();

        $this->line($nivel === 'error' ? "<error> ✘ {$titulo} </error>" : "<comment>! {$titulo}</comment>");

        foreach ($lineas->take(25) as $linea) {
            $this->line("    - {$linea}");
        }

        if ($lineas->count() > 25) {
            $this->line('    … y ' . ($lineas->count() - 25) . ' más');
        }
    }
}