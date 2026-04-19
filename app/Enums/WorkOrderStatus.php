<?php

namespace App\Enums;

/**
 * Enum WorkOrderStatus
 *
 * [SOLID - OCP] Máquina de estados de la Orden de Trabajo.
 * Cada estado tiene transiciones válidas definidas, evitando
 * transiciones ilegales y garantizando la integridad del flujo.
 */
enum WorkOrderStatus: string
{
    case DRAFT       = 'draft';
    case REGISTERED  = 'registered';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case DELIVERED   = 'delivered';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT       => 'Borrador',
            self::REGISTERED  => 'Registrada',
            self::IN_PROGRESS => 'En Progreso',
            self::COMPLETED   => 'Completada',
            self::DELIVERED   => 'Entregada',
            self::CANCELLED   => 'Cancelada',
        };
    }

    /**
     * Color CSS para badges y UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT       => 'gray',
            self::REGISTERED  => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED   => 'green',
            self::DELIVERED   => 'indigo',
            self::CANCELLED   => 'red',
        };
    }

    /**
     * Icono heroicons para la UI.
     */
    public function icon(): string
    {
        return match ($this) {
            self::DRAFT       => 'pencil-square',
            self::REGISTERED  => 'clipboard-document-check',
            self::IN_PROGRESS => 'arrow-path',
            self::COMPLETED   => 'check-circle',
            self::DELIVERED   => 'truck',
            self::CANCELLED   => 'x-circle',
        };
    }

    /**
     * Define las transiciones válidas desde el estado actual.
     * [SOLID - SRP] Solo esta lógica vive aquí.
     *
     * @return array<WorkOrderStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT       => [self::REGISTERED, self::CANCELLED],
            self::REGISTERED  => [self::IN_PROGRESS, self::CANCELLED],
            self::IN_PROGRESS => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED   => [self::DELIVERED],
            self::DELIVERED   => [],
            self::CANCELLED   => [],
        };
    }

    /**
     * Verifica si se puede transicionar al estado dado.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }
}
