<?php

namespace App\Enums;

/**
 * Enum Priority
 *
 * Niveles de prioridad para las Órdenes de Trabajo.
 * Permite al área de Administración priorizar trabajos urgentes.
 */
enum Priority: string
{
    case LOW    = 'low';
    case NORMAL = 'normal';
    case HIGH   = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW    => 'Baja',
            self::NORMAL => 'Normal',
            self::HIGH   => 'Alta',
            self::URGENT => 'Urgente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW    => 'gray',
            self::NORMAL => 'blue',
            self::HIGH   => 'orange',
            self::URGENT => 'red',
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
