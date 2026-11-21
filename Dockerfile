# syntax=docker/dockerfile:1

# Imagem de desenvolvimento da aplicação Laravel (biblioteca_virtual).
# Multi-stage build: dependências PHP e assets do front-end são preparados em
# estágios separados; a imagem final recebe só o resultado.
# Dentro de cada estágio, o que muda menos fica em cima, para aproveitar o cache.

ARG PHP_VERSION=8.5
ARG NODE_VERSION=24

FROM composer:2 AS composer

# ---------------------------------------------------------------------------
# base: PHP CLI + extensões usadas pela aplicação
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-cli-alpine AS base

# Bibliotecas de runtime ficam; as de compilação são removidas na mesma camada.
RUN apk add --no-cache libpq icu-libs libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev icu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pcntl intl zip bcmath \
    && apk del .build-deps

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# vendor: dependências PHP (só é refeito quando composer.json/lock mudam)
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# ---------------------------------------------------------------------------
# assets: compilação do front-end com Vite (só é refeito quando o front muda)
# ---------------------------------------------------------------------------
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Views de paginação do framework, referenciadas por @source no app.css
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
    ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# app: imagem final
# ---------------------------------------------------------------------------
FROM base AS app

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && chmod +x /usr/local/bin/entrypoint

USER www-data

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
