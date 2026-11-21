<?php

/*
| Parâmetros das regras de negócio (Requisitos Levantados → Parâmetros das Regras).
*/

return [

    // R1, R2: prazo de devolução, contado da retirada ou da renovação
    'prazo_emprestimo_dias' => 5,

    // R2: número máximo de renovações por empréstimo
    'max_renovacoes' => 3,

    // R4: valor da multa por dia de atraso, em reais
    'multa_por_dia' => '0.50',

    // R5, R15: empréstimos ativos simultâneos por leitor
    'limite_emprestimos_ativos' => 5,

    // R9, R11: prazo para retirar um exemplar reservado
    'prazo_retirada_dias' => 3,

];
