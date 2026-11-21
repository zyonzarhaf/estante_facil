<?php

namespace App\Enums;

/**
 * Motivos pelos quais uma operação é recusada (RF17).
 */
enum MotivoRecusa: string
{
    case LeitorBloqueado = 'LEITOR_BLOQUEADO';
    case LimiteAtingido = 'LIMITE_ATINGIDO';
    case ExemplarIndisponivel = 'EXEMPLAR_INDISPONIVEL';
    case ExemplarOcupado = 'EXEMPLAR_OCUPADO';
    case ReservaDuplicada = 'RESERVA_DUPLICADA';
    case ReservaNaoCancelavel = 'RESERVA_NAO_CANCELAVEL';
    case EmprestimoInvalido = 'EMPRESTIMO_INVALIDO';
    case LimiteRenovacoes = 'LIMITE_RENOVACOES';
    case ExemplarComFila = 'EXEMPLAR_COM_FILA';
    case PrazoVencido = 'PRAZO_VENCIDO';
    case SemMultaPendente = 'SEM_MULTA_PENDENTE';

    public function mensagem(): string
    {
        return match ($this) {
            self::LeitorBloqueado => 'O leitor está bloqueado: há multa pendente, empréstimo atrasado ou o cadastro está inativo.',
            self::LimiteAtingido => 'O leitor já tem '.config('biblioteca.limite_emprestimos_ativos').' empréstimos ativos.',
            self::ExemplarIndisponivel => 'O exemplar está indisponível.',
            self::ExemplarOcupado => 'O exemplar não está livre para empréstimo.',
            self::ReservaDuplicada => 'O leitor já está com este exemplar ou já tem uma reserva na fila dele.',
            self::ReservaNaoCancelavel => 'Só é possível cancelar uma reserva que está na fila.',
            self::EmprestimoInvalido => 'Esta operação não é válida para a situação atual do empréstimo.',
            self::LimiteRenovacoes => 'O empréstimo já foi renovado '.config('biblioteca.max_renovacoes').' vezes.',
            self::ExemplarComFila => 'Há leitores na fila deste exemplar; não é possível renovar.',
            self::PrazoVencido => 'O prazo para retirada deste exemplar já venceu.',
            self::SemMultaPendente => 'Este empréstimo não tem multa pendente.',
        };
    }
}
