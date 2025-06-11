<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ScaffoldCoreStructure;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\InputTrait;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class DefineContainerizedEnvironmentCommand extends Command
{
    use InputTrait;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold-core-structure:define-containerized-environment
                            {php : Specify the PHP version for the project (e.g., 8.2).}
                            {--with-ca-file= : Path to a custom CA certificate to trust within the container (e.g, certs/certificate.pem).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define Containerized Environment';

    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $php = $this->getValidatedPhpVersion();
        $caFilepath = $this->getValidatedCaFilePath($generator->fs);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator, $php, $caFilepath) {
            $generator
                ->appendToFile('docker/act/Dockerfile', $this->generateActDockerfileContent($caFilepath))
                ->appendToFile('docker/composer/Dockerfile', $this->generateComposerDockerfileContent($php, $caFilepath))
                ->generate($this->laravel->basePath());
        });

        return 0;
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

    private function generateComposerDockerfileContent(string $php, ?string $caFilepath): string
    {
        $content = <<< DOCKERFILE
FROM php:$php-fpm-alpine

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
