<?php

namespace App\Enums;

/**
 * Situação derivada da multa: não é armazenada, sai de `data_pagamento`.
 */
enum SituacaoMulta: string
{
    case Pendente = 'PENDENTE';
    case Paga = 'PAGA';

    public function rotulo(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Paga => 'Paga',
        };
    }
}
