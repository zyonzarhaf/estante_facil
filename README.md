# Estante Fácil

Sistema de empréstimo de livros para uma biblioteca física. Ele liga o acervo, o bibliotecário e o leitor.

- O **bibliotecário** atende no balcão, registrando empréstimos, retiradas, devoluções e pagamentos de multa, e mantém o acervo e o cadastro de leitores.
- O **leitor** consulta o acervo, reserva exemplares, renova empréstimos e acompanha a própria conta.
- Quando o exemplar reservado está emprestado, o leitor entra numa **fila**. Na devolução, o exemplar vai para o primeiro da fila que não está bloqueado, e esse leitor recebe um aviso por e-mail.

Feito com Laravel 13, PHP 8.5 e PostgreSQL 17, rodando em Docker.

## Como rodar localmente

Você só precisa de [Docker](https://docs.docker.com/get-docker/) com o Docker Compose.

```bash
git clone https://github.com/zyonzarhaf/estante_facil.git
cd estante_facil
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

O `.env.example` já vem pronto para desenvolvimento, então não precisa editar nada.

O seeder cria um acervo e leitores fictícios e simula alguns meses de circulação. Para apagar tudo e gerar os dados de novo:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Endereços

| Serviço | Endereço |
|---|---|
| Aplicação | http://localhost:8000 |
| Mailpit (e-mails enviados) | http://localhost:8025 |
| PostgreSQL | `localhost:5432` (usuário e senha `biblioteca`) |

Se alguma porta já estiver em uso, troque `APP_PORT`, `MAILPIT_UI_PORT`, `MAILPIT_SMTP_PORT` ou `DB_FORWARD_PORT` no `.env`.

## Contas de demonstração

A senha de todas é `senha-secreta`.

| Perfil | E-mail |
|---|---|
| Bibliotecário | `bibliotecaria@biblioteca.local` |
| Leitor | `leitor@biblioteca.local` |

## Testes

Os testes usam um banco separado, o `estante_facil_testes`, criado junto com o contêiner do PostgreSQL.

```bash
docker compose exec app php vendor/bin/phpunit
```

## Aviso

O `APP_KEY` do `.env.example` é público e serve só para desenvolvimento. Para publicar em produção, gere outro com `php artisan key:generate`.
