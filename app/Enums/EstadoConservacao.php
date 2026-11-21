<?php

namespace App\Enums;

enum EstadoConservacao: string
{
    case Novo = 'NOVO';
    case Bom = 'BOM';
    case Regular = 'REGULAR';
    case Ruim = 'RUIM';

    public function rotulo(): string
    {
        return match ($this) {
            self::Novo => 'Novo',
            self::Bom => 'Bom',
            self::Regular => 'Regular',
            self::Ruim => 'Ruim',
        };
    }
}
