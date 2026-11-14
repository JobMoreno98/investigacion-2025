<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_editable' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Los sub-formularios viven y mueren con su registro padre.
        static::deleting(function (Entry $entry) {
            if ($entry->isForceDeleting()) {
                $entry->children()->withTrashed()->get()->each->forceDelete();
            } else {
                $entry->children->each->delete();
            }
        });

        static::restoring(function (Entry $entry) {
            $entry->children()->onlyTrashed()->get()->each->restore();
        });
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclos::class);
    }

    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id');
    }

    /** Registro padre (si este registro es un sub-formulario). */
    public function parent()
    {
        return $this->belongsTo(Entry::class, 'parent_entry_id');
    }

    /** Sub-formularios anidados. */
    public function children()
    {
        return $this->hasMany(Entry::class, 'parent_entry_id');
    }

    /* ---------------------------------------------------------------
     | Scopes: la forma única de preguntar "qué registros tiene X"
     | --------------------------------------------------------------- */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInSection(Builder $query, int $sectionId): Builder
    {
        return $query->where('section_id', $sectionId);
    }

    /** Solo registros de primer nivel (excluye sub-formularios). */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_entry_id');
    }
}