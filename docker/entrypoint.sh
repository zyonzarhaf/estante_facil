#!/bin/sh
# Processos da aplicação em desenvolvimento, todos no mesmo contêiner:
# - agendador (R12: encerrar retiradas vencidas);
# - worker da fila de trabalhos (R18: aviso por e-mail);
# - servidor HTTP embutido do Laravel, em primeiro plano.
# Agendador e worker são reiniciados se terminarem (por exemplo, antes das
# migrations existirem).
set -e

keep_running() {
    while true; do
        "$@" || true
        echo "[entrypoint] '$*' terminou; reiniciando em 10s" >&2
        sleep 10
    done
}

keep_running php artisan schedule:work &
keep_running php artisan queue:work --tries=3 &

exec php artisan serve --host=0.0.0.0 --port=8000
