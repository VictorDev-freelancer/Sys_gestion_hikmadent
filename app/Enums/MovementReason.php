<?php

namespace App\Enums;

/**
 * Enum MovementReason
 *
 * [CLEAN CODE] Motivo específico del movimiento de inventario.
 * Complementa a MovementType con granularidad de negocio.
 *
 * Ejemplo: MovementType::EXIT + MovementReason::WORK_ORDER_CONSUMPTION
 */
enum MovementReason: string
{
    case PURCHASE               = 'purchase';
    case WORK_ORDER_CONSUMPTION = 'work_order_consumption';
    case ADJUSTMENT_ADD         = 'adjustment_add';
    case ADJUSTMENT_SUB         = 'adjustment_sub';
    case RETURN_FROM_ORDER      = 'return_from_order';
    case INITIAL_STOCK          = 'initial_stock';
    case DAMAGE                 = 'damage';
    case EXPIRATION             = 'expiration';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE               => 'Compra',
            self::WORK_ORDER_CONSUMPTION => 'Consumo por Orden de Trabajo',
            self::ADJUSTMENT_ADD         => 'Ajuste positivo',
            self::ADJUSTMENT_SUB         => 'Ajuste negativo',
            self::RETURN_FROM_ORDER      => 'Devolución de Orden',
            self::INITIAL_STOCK          => 'Stock inicial',
            self::DAMAGE                 => 'Daño / Merma',
            self::EXPIRATION             => 'Vencimiento',
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
