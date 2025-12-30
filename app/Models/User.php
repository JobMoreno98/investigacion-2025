<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // App/Models/User.php

    public function hasUpdatedProfileThisYear(): bool
    {
        // 1. Buscamos la sección de Datos Generales (la que no es repetible)
        $profileSection = Sections::where('is_repeatable', false)->first();

        if (! $profileSection) {
            return false;
        }

        // 2. Buscamos el entry del usuario para esa sección
        $entry = Entry::where('user_id', $this->id)
            ->whereHas('answers.question', fn ($q) => $q->where('section_id', $profileSection->id))
            ->first();

        // 3. Reglas de Validación:
        // - Si no existe el entry -> Falso (nunca ha llenado datos)
        if (! $entry) {
            return false;
        }

        // - Si el año de actualización es MENOR al año actual -> Falso (debe actualizar)
        if ($entry->updated_at->year < now()->year) {
            return false;
        }

        // - Si llegamos aquí, es que existe y se actualizó este año -> Verdadero
        return true;
    }
}
