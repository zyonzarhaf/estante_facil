<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * RF18: aviso ao leitor de que sua reserva foi atendida.
 */
class AvisoReservaAtendida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reserva $reserva) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua reserva foi atendida',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.aviso-reserva-atendida',
            with: [
                'nome' => $this->reserva->leitor->usuario->nome,
                'obra' => $this->reserva->exemplar->edicao->obra->titulo,
                'tombo' => $this->reserva->exemplar->codigo_tombo,
                'dataLimite' => $this->reserva->emprestimos->first()?->data_limite_retirada,
            ],
        );
    }
}
