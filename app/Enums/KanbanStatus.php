<?php

namespace App\Enums;

/**
 * Enum KanbanStatus
 *
 * Representa las 3 columnas del tablero Kanban (CANVA)
 * especificadas en el prompt: Inicio, En Proceso, Finalizado.
 */
enum KanbanStatus: string
{
    case PENDING     = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'Inicio',
            self::IN_PROGRESS => 'En Proceso',
            self::COMPLETED   => 'Finalizado',
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
