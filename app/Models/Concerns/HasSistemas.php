<?php

namespace App\Models\Concerns;

use App\Models\Sistemas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Pertenencia a uno o varios sistemas mediante una tabla pivote.
 *
 * El modelo que lo use declara su pivote:
 *   protected function sistemaPivotTable(): string { return 'section_sistema'; }
 *   protected function sistemaPivotKey(): string   { return 'section_id'; }
 */
trait HasSistemas
{
    abstract protected function sistemaPivotTable(): string;

    abstract protected function sistemaPivotKey(): string;

    public function sistemas(): BelongsToMany
    {
        return $this->belongsToMany(Sistemas::class, $this->sistemaPivotTable(), $this->sistemaPivotKey(), 'sistema_id');
    }

    /** Registros que pertenecen a alguno de los sistemas indicados (slug o lista de slugs). */
    public function scopeForSistema(Builder $query, string|array $slugs): Builder
    {
        return $query->whereHas('sistemas', fn (Builder $q) => $q->whereIn('sistemas.slug', (array) $slugs));
    }

    /** Registros que NO pertenecen a ninguno de los sistemas indicados. */
    public function scopeNotInSistema(Builder $query, string|array $slugs): Builder
    {
        return $query->whereDoesntHave('sistemas', fn (Builder $q) => $q->whereIn('sistemas.slug', (array) $slugs));
    }

    /** Reemplaza los sistemas del registro por los slugs indicados. */
    public function syncSistemas(array $slugs): void
    {
        $this->sistemas()->sync(
            Sistemas::query()->whereIn('slug', $slugs)->pluck('id')->all()
        );
    }

    public function perteneceASistema(string $slug): bool
    {
        return $this->sistemas->contains('slug', $slug);
    }
}