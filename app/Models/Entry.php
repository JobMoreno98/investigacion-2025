<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    protected $guarded = [];

    use SoftDeletes; // <--- Activa la magia
    protected $dates = ['deleted_at'];
    protected $casts = [
        'is_editable' => 'boolean',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación para obtener la sección
    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id');
    }

    // Relación VITAL para traer los sub-formularios anidados automáticamente
    public function children()
    {
        return $this->hasMany(Entry::class, 'parent_entry_id');
    }
}
