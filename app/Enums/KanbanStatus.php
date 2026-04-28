<?php

namespace App\Enums;

/**
 * Enum KanbanStatus
 *
 * Representa las 3 columnas del tablero Kanban (CANVA)
 * especificadas en el prompt: Asignado, En desarrollo, Completado.
 */
enum KanbanStatus: string
{
    case PENDING     = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'Asignado',
            self::IN_PROGRESS => 'En desarrollo',
            self::COMPLETED   => 'Completado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING     => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED   => 'green',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }
}
