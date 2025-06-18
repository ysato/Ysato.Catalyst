FROM php:8.2-fpm-alpine

RUN apk add --no-cache ca-certificates
COPY ./certs/certificate.pem /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf
RUN apk add --no-cache linux-headers

RUN pecl install https://pecl.php.net/get/xdebug-3.4.3.tgz
RUN pecl install pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html
