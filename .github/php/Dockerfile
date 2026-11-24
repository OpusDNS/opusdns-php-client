ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli-alpine

# Composer release and the SHA-256 of its composer.phar.
ARG COMPOSER_VERSION=2.11.3
ARG COMPOSER_SHA256=04f8b003da4e3a2459056591fbfebd02b4dc3e5adeb158616b90cf55a65113ec

RUN apk add --no-cache unzip \
    && curl -fsSL -o /usr/local/bin/composer "https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar" \
    && echo "${COMPOSER_SHA256}  /usr/local/bin/composer" | sha256sum -c - \
    && chmod +x /usr/local/bin/composer
