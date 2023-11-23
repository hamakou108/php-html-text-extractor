FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer
