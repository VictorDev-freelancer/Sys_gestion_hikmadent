<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Client
 *
 * [SOLID - SRP] Representa exclusivamente al cliente/doctor que solicita
 * servicios al laboratorio dental HIKMADENT.
 *
 * Un cliente puede generar múltiples Órdenes de Trabajo, cada una
 * recorriendo las áreas operativas del laboratorio.
 *
 * @property int    $id
 * @property string $name           Nombre completo del doctor/cliente
 * @property string $phone          Teléfono de contacto
 * @property string $email          Correo electrónico
 * @property string $doctor_name    Dr.(a) responsable (puede diferir del cliente)
 * @property string $clinic_name    Nombre del consultorio
 * @property string $notes          Notas adicionales
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'doctor_name',
        'clinic_name',
        'notes',
    ];

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Un cliente puede tener muchas órdenes de trabajo.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
