FROM php:{{ php }}-fpm-alpine
{% if with_ca is not null %}

RUN apk add --no-cache ca-certificates
COPY {{ with_ca }} /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates
{% endif %}

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf
RUN apk add --no-cache linux-headers

RUN pecl install https://pecl.php.net/get/xdebug-3.4.3.tgz
RUN pecl install pcov
RUN docker-php-ext-enable xdebug pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html
