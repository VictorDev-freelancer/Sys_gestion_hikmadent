<?php

namespace App\Enums;

/**
 * Enum ProstheticType
 *
 * [CLEAN CODE] Reemplaza "magic strings" dispersas en validaciones y formularios.
 * Cada caso mapea a un valor persistible en BD (`backed: string`), garantizando
 * integridad referencial sin necesidad de tabla auxiliar.
 *
 * Los tipos protésicos provienen directamente de la especificación de negocio
 * del área de Administración de HIKMADENT.
 */
enum ProstheticType: string
{
    case METAL_PORCELANA     = 'metal_porcelana';
    case IMPLANTES_MULTIPLES = 'implantes_multiples';
    case ENCERADO            = 'encerado';
    case IPS_EMAX            = 'ips_emax';
    case ZIRCONIO            = 'zirconio';
    case IMPLANTES           = 'implantes';
    case CEROMERO            = 'ceromero';
    case OTRO                = 'otro';

    /**
     * Etiqueta legible para mostrar en formularios y reportes.
     */
    public function label(): string
    {
        return match ($this) {
            self::METAL_PORCELANA     => 'Metal Porcelana',
            self::IMPLANTES_MULTIPLES => 'Implantes Múltiples',
            self::ENCERADO            => 'Encerado',
            self::IPS_EMAX            => 'I.P.S. EMAX',
            self::ZIRCONIO            => 'Zirconio',
            self::IMPLANTES           => 'Implantes',
            self::CEROMERO            => 'Cerómero',
            self::OTRO                => 'Otro',
        };
    }

    /**
     * Retorna todos los casos como array asociativo [value => label].
     * Útil para poblar selects en formularios Livewire.
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }
}
