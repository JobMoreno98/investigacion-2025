<?php

namespace App\Livewire;

use App\Models\Categorias;
use App\Models\Sistemas;
use Livewire\Component;

class NavigationBar extends Component
{
    public function render()
    {
        $slug = Sistemas::slugActual();

        // Categorías compartidas con este sistema que tienen al menos una sección activa en él.
        $enlaces = Categorias::forSistema($slug)
            ->whereHas('secciones', fn ($q) => $q->forSistema($slug)->where('is_active', true))
            ->orderBy('id')
            ->get();

        return view('livewire.navigation-bar', ['enlaces' => $enlaces]);
    }
}