<?php

namespace App\Enums;

/**
 * Enum FresMaterialType
 *
 * Materias primas del área de Fresado según el prompt:
 * ZIRCONIO, PMMA, EMAX, RESINA.
 */
enum FresMaterialType: string
{
    case ZIRCONIO = 'zirconio';
    case PMMA     = 'pmma';
    case EMAX     = 'emax';
    case RESINA   = 'resina';

    public function label(): string
    {
        return match ($this) {
            self::ZIRCONIO => 'Zirconio',
            self::PMMA     => 'PMMA',
            self::EMAX     => 'EMAX',
            self::RESINA   => 'Resina',
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
