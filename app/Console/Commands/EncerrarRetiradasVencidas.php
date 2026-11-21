<?php

namespace App\Console\Commands;

use App\Services\ServicoDeEmprestimo;
use Illuminate\Console\Command;

/**
 * R12: encerra os empréstimos AGUARDANDO_RETIRADA com prazo vencido e atende
 * a fila dos exemplares liberados. Agendado uma vez por dia.
 */
class EncerrarRetiradasVencidas extends Command
{
    protected $signature = 'biblioteca:encerrar-retiradas-vencidas';

    protected $description = 'Encerra as retiradas vencidas e atende a fila dos exemplares liberados (R12)';

    public function handle(ServicoDeEmprestimo $servicoDeEmprestimo): int
    {
        $encerrados = $servicoDeEmprestimo->encerrarRetiradasVencidas();

        $this->info("Retiradas vencidas encerradas: {$encerrados}.");

        return self::SUCCESS;
    }
}
