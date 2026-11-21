<?php

namespace App\Enums;

/**
 * Situação derivada do exemplar: não é armazenada, sai de `indisponivel`
 * e do empréstimo em curso.
 */
enum SituacaoCirculacao: string
{
    case Disponivel = 'DISPONIVEL';
    case Emprestado = 'EMPRESTADO';
    case AguardandoRetirada = 'AGUARDANDO_RETIRADA';
    case Indisponivel = 'INDISPONIVEL';

    public function rotulo(): string
    {
        return match ($this) {
            self::Disponivel => 'Disponível',
            self::Emprestado => 'Emprestado',
            self::AguardandoRetirada => 'Aguardando retirada',
            self::Indisponivel => 'Indisponível',
        };
    }
}
