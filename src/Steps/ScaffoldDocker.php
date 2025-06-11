<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Ysato\Catalyst\Input;

class ScaffoldDocker implements StepInterface
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly TemporaryDirectory $sandbox,
        private readonly Input $input
    ) {
    }

    public function execute(): void
    {
        $this->fs->appendToFile(
            $this->sandbox->path('docker/act/Dockerfile'),
            $this->generateActDockerfileContent($this->input->caFilePath)
        );
        $this->fs->appendToFile(
            $this->sandbox->path('docker/composer/Dockerfile'),
            $this->generateComposerDockerfileContent($this->input->caFilePath)
        );
    }

    private function generateActDockerfileContent(?string $caFilepath): string
    {
        $content = <<< DOCKERFILE
FROM shivammathur/node:latest

DOCKERFILE;

        if (! $caFilepath) {
            return $content;
        }

        return $content . <<< DOCKERFILE

RUN apt-get update && apt-get install -y ca-certificates
COPY $caFilepath /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates

DOCKERFILE;
    }

    private function generateComposerDockerfileContent(?string $caFilepath): string
    {
        $content = <<< DOCKERFILE
FROM php:__Php__-fpm-alpine

DOCKERFILE;

        if ($caFilepath) {
            $content .= <<< DOCKERFILE

RUN apk add --no-cache ca-certificates
COPY $caFilepath /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates

DOCKERFILE;
        }

        return $content . <<< 'DOCKERFILE'

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf
RUN apk add --no-cache linux-headers

RUN pecl install https://pecl.php.net/get/xdebug-3.4.3.tgz
RUN pecl install pcov
RUN docker-php-ext-enable xdebug pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html

DOCKERFILE;
    }
}
