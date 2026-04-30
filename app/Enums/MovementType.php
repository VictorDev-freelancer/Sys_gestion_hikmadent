<?php

namespace App\Enums;

/**
 * Enum MovementType
 *
 * [CLEAN CODE] Define los tipos de movimiento del Kardex.
 * Cada tipo afecta el stock de forma diferente:
 * - ENTRY/RETURN → incrementan stock
 * - EXIT → decrementa stock
 * - ADJUSTMENT → puede incrementar o decrementar
 */
enum MovementType: string
{
    case ENTRY      = 'entry';
    case EXIT       = 'exit';
    case ADJUSTMENT = 'adjustment';
    case RETURN     = 'return';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY      => 'Entrada',
            self::EXIT       => 'Salida',
            self::ADJUSTMENT => 'Ajuste',
            self::RETURN     => 'Devolución',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ENTRY      => 'green',
            self::EXIT       => 'red',
            self::ADJUSTMENT => 'yellow',
            self::RETURN     => 'blue',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ENTRY      => 'arrow-down-tray',
            self::EXIT       => 'arrow-up-tray',
            self::ADJUSTMENT => 'adjustments-horizontal',
            self::RETURN     => 'arrow-uturn-left',
        };
    }

    /**
     * ¿Este tipo de movimiento incrementa el stock?
     */
    public function isIncoming(): bool
    {
        return in_array($this, [self::ENTRY, self::RETURN]);
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }
}
