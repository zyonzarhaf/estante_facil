<x-mail::message>
# Sua reserva foi atendida

Olá, {{ $nome }}.

O exemplar **{{ $tombo }}** de *{{ $obra }}* foi separado para você.

Retire-o no balcão da biblioteca até **{{ $dataLimite?->format('d/m/Y') }}**. Depois dessa data, a reserva perde a validade e o exemplar passa para o próximo leitor da fila.

<x-mail::button :url="route('leitor.painel')">
Ver minhas reservas
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
