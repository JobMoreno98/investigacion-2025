<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questions;
use App\Models\Categorias;

class Sections extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    public function categorias()
    
    {
        return $this->belongsTo(categorias::class, 'categoria_id');
    }

    public function questions()
    {
        return $this->hasMany(Questions::class, 'section_id')->orderBy('sort_order');
    }
}
