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
        $this->task(function () use ($php, $generator, $caFilepath) {
            $caAlpineContent = $caFilepath ? $this->getCaAlpineContent($caFilepath) : '';
            $caDebianContent = $caFilepath ? $this->getCaDebianContent($caFilepath) : '';

            $generator
                ->replacePlaceHolder(
                    ['__Php__', "__Ca_Alpine__", "__Ca_Debian__\n"],
                    [$php, $caAlpineContent, $caDebianContent]
                )
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    private function getCaAlpineContent(string $caFilepath)
    {
        return <<< EOF

RUN apt-get update && apt-get install -y ca-certificates
COPY $caFilepath /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates

EOF;
    }

    private function getCaDebianContent(string $caFilepath)
    {
        return <<< EOF

RUN apk add --no-cache ca-certificates
COPY $caFilepath /usr/local/share/ca-certificates/certificate.crt
RUN update-ca-certificates

EOF;
    }
}
