<?php

namespace App\Enums;

enum SituacaoEmprestimo: string
{
    case AguardandoRetirada = 'AGUARDANDO_RETIRADA';
    case Ativo = 'ATIVO';
    case Encerrado = 'ENCERRADO';

    public function rotulo(): string
    {
        return match ($this) {
            self::AguardandoRetirada => 'Aguardando retirada',
            self::Ativo => 'Ativo',
            self::Encerrado => 'Encerrado',
        };
    }
}
