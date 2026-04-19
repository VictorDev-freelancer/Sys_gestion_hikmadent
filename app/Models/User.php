<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasProfilePhoto, TwoFactorAuthenticatable;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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

    /* ------------------------------------------------------------------ */
    /*  RELACIONES CON EL DOMINIO HIKMADENT                                */
    /* ------------------------------------------------------------------ */

    /**
     * Órdenes de trabajo creadas por este usuario.
     */
    public function createdWorkOrders(): HasMany
    {
        return $this->hasMany(\App\Models\WorkOrder::class, 'created_by');
    }

    /**
     * Órdenes asignadas como TPD responsable.
     */
    public function assignedWorkOrders(): HasMany
    {
        return $this->hasMany(\App\Models\WorkOrder::class, 'assigned_tpd_id');
    }

    /**
     * Áreas de trabajo asignadas (como técnico).
     */
    public function workOrderAreas(): HasMany
    {
        return $this->hasMany(\App\Models\WorkOrderArea::class, 'assigned_to');
    }

    /**
     * Áreas de trabajo supervisadas (como doctor/supervisor).
     */
    public function supervisedAreas(): HasMany
    {
        return $this->hasMany(\App\Models\WorkOrderArea::class, 'supervisor_id');
    }
}
