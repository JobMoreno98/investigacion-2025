<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Genera un `slug` único al crear el registro (a partir de slugSource()) si no se indicó uno.
 * El slug es el identificador estable: no cambia aunque se edite el título.
 *
 *   protected function slugSource(): string { return 'title'; }
 */
trait HasSlug
{
    abstract protected function slugSource(): string;

    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlugFor((string) $model->{$model->slugSource()}, $model);
            }
        });
    }

    protected static function uniqueSlugFor(string $text, Model $model): string
    {
        $base = Str::limit(Str::slug($text), 90, '') ?: Str::lower(class_basename($model));
        $slug = $base;
        $suffix = 2;

        // withoutGlobalScopes: el índice único también cuenta los registros con soft delete.
        while (static::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    public static function idBySlug(string $slug): ?int
    {
        return static::query()->where('slug', $slug)->value('id');
    }
}