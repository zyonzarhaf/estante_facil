<?php

use Illuminate\Support\Facades\Schedule;

// R12: única regra que depende só da passagem do tempo (RNF09)
Schedule::command('biblioteca:encerrar-retiradas-vencidas')->dailyAt('00:05');
