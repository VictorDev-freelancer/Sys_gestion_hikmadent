<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception InsufficientStockException
 *
 * [SOLID - SRP] Excepción de dominio específica para cuando
 * se intenta consumir más stock del disponible.
 *
 * Proporciona contexto enriquecido: SKU, disponible, requerido.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $sku,
        public readonly float $available,
        public readonly float $requested,
        string $message = '',
    ) {
        if (empty($message)) {
            $message = "Stock insuficiente para [{$sku}]. "
                     . "Disponible: {$available}, Requerido: {$requested}";
        }

        parent::__construct($message);
    }
}
