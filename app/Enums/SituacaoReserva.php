<?php

namespace App\Enums;

enum SituacaoReserva: string
{
    case NaFila = 'NA_FILA';
    case Atendida = 'ATENDIDA';
    case Cancelada = 'CANCELADA';

    public function rotulo(): string
    {
        return match ($this) {
            self::NaFila => 'Na fila',
            self::Atendida => 'Atendida',
            self::Cancelada => 'Cancelada',
        };
    }
}
