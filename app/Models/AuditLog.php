<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model AuditLog — Log de Auditoría Polimórfico
 *
 * [SOLID - SRP] Responsabilidad única: registrar QUIÉN cambió QUÉ,
 * CUÁNDO y con qué valores (old → new).
 *
 * Relación polimórfica permite auditar cualquier modelo del sistema
 * (WorkOrder, WorkOrderArea, Client, etc.) sin tablas separadas.
 */
class AuditLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Entidad auditada (polimórfica).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario que realizó la acción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
