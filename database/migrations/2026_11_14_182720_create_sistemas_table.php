<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Catálogo de sistemas + pertenencia relacional de categorías, secciones y ciclos.
 *
 * Reemplaza (sin borrarlas todavía):
 *   - categorias.sistema   (JSON en longtext)
 *   - ciclos.sistemas      (JSON en longtext)
 *   - sections.investigacion (una columna booleana por sistema)
 *
 * Además agrega `slug` a sections y categorias: identificador estable para no depender
 * de títulos ('Proyectos de Investigación', 'Datos Generales') en el código ni en las vistas.
 *
 * Las columnas viejas se conservan hasta validar; se eliminan en una migración posterior.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->assertInnoDb(['categorias', 'sections', 'ciclos']);

        Schema::create('sistemas', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $this->createPivot('categoria_sistema', 'categoria_id', 'categorias');
        $this->createPivot('section_sistema', 'section_id', 'sections');
        $this->createPivot('ciclo_sistema', 'ciclo_id', 'ciclos');

        Schema::table('sections', fn (Blueprint $table) => $table->string('slug', 100)->nullable()->after('title'));
        Schema::table('categorias', fn (Blueprint $table) => $table->string('slug', 100)->nullable()->after('titulo'));

        DB::transaction(function () {
            $this->backfillSistemas();
            $this->fillSlugs('sections', 'title');
            $this->fillSlugs('categorias', 'titulo');
        });

        Schema::table('sections', fn (Blueprint $table) => $table->unique('slug'));
        Schema::table('categorias', fn (Blueprint $table) => $table->unique('slug'));
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::dropIfExists('ciclo_sistema');
        Schema::dropIfExists('section_sistema');
        Schema::dropIfExists('categoria_sistema');
        Schema::dropIfExists('sistemas');
    }

    /** Las llaves foráneas exigen InnoDB en las tablas padre. */
    private function assertInnoDb(array $tables): void
    {
        $wrong = DB::table('information_schema.tables')
            ->where('table_schema', DB::getDatabaseName())
            ->whereIn('table_name', $tables)
            ->where('engine', '!=', 'InnoDB')
            ->pluck('table_name');

        if ($wrong->isNotEmpty()) {
            throw new RuntimeException(
                'Estas tablas no son InnoDB y deben convertirse antes de migrar: ' . $wrong->implode(', ')
            );
        }
    }

    private function createPivot(string $name, string $key, string $parent): void
    {
        Schema::create($name, function (Blueprint $table) use ($key, $parent) {
            $table->unsignedBigInteger($key);
            $table->unsignedBigInteger('sistema_id');

            $table->primary([$key, 'sistema_id']);   // "sistemas de X"
            $table->index('sistema_id');             // "X de un sistema"

            $table->foreign($key)->references('id')->on($parent)->cascadeOnDelete();
            $table->foreign('sistema_id')->references('id')->on('sistemas')->cascadeOnDelete();
        });
    }

    private function backfillSistemas(): void
    {
        $now = now();
        $ids = []; // slug => id

        $ensure = function (string $raw) use (&$ids, $now): int {
            $slug = Str::slug($raw);

            return $ids[$slug] ??= (int) (
                DB::table('sistemas')->where('slug', $slug)->value('id')
                ?? DB::table('sistemas')->insertGetId([
                    'slug' => $slug,
                    'nombre' => Str::headline($slug),
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        };

        // Categorías: categorias.sistema (JSON) -> categoria_sistema
        $categoriaSistemas = []; // categoria_id => [sistema_id]

        foreach (DB::table('categorias')->get(['id', 'sistema']) as $row) {
            $sistemaIds = $this->decodeList($row->sistema, $ensure);
            $categoriaSistemas[$row->id] = $sistemaIds;
            $this->insertPivot('categoria_sistema', 'categoria_id', $row->id, $sistemaIds);
        }

        // Ciclos: ciclos.sistemas (JSON) -> ciclo_sistema
        foreach (DB::table('ciclos')->get(['id', 'sistemas']) as $row) {
            $this->insertPivot('ciclo_sistema', 'ciclo_id', $row->id, $this->decodeList($row->sistemas, $ensure));
        }

        // Secciones: heredan los sistemas de su categoría (así se comportan hoy) y, si tienen
        // el indicador `investigacion`, se les agrega ese sistema.
        // REVISAR esta regla con la consulta de verificación antes de dar la migración por buena.
        foreach (DB::table('sections')->get(['id', 'categoria_id', 'investigacion']) as $row) {
            $sistemaIds = $categoriaSistemas[$row->categoria_id] ?? [];

            if ($row->investigacion) {
                $sistemaIds[] = $ensure('investigacion');
            }

            $this->insertPivot('section_sistema', 'section_id', $row->id, $sistemaIds);
        }
    }

    /** Convierte '["sia","investigacion"]' en ids de sistemas, ignorando valores inválidos. */
    private function decodeList(?string $json, callable $ensure): array
    {
        $list = json_decode((string) $json, true);

        if (! is_array($list)) {
            return [];
        }

        return collect($list)
            ->filter(fn ($value) => is_string($value) && Str::slug($value) !== '')
            ->map(fn ($value) => $ensure($value))
            ->unique()
            ->values()
            ->all();
    }

    private function insertPivot(string $table, string $key, int $id, array $sistemaIds): void
    {
        $rows = collect($sistemaIds)
            ->unique()
            ->map(fn ($sistemaId) => [$key => $id, 'sistema_id' => $sistemaId])
            ->all();

        if ($rows) {
            DB::table($table)->insertOrIgnore($rows);
        }
    }

    /** slug único a partir del título (incluye filas con soft delete, porque el índice único las cuenta). */
    private function fillSlugs(string $table, string $sourceColumn): void
    {
        $used = [];

        foreach (DB::table($table)->orderBy('id')->get(['id', $sourceColumn]) as $row) {
            $base = Str::limit(Str::slug((string) $row->{$sourceColumn}), 90, '') ?: Str::singular($table);
            $slug = $base;
            $suffix = 2;

            while (isset($used[$slug])) {
                $slug = $base . '-' . $suffix++;
            }

            $used[$slug] = true;

            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};