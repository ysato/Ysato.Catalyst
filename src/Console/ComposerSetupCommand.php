<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\PhpVersionAskable;
use Ysato\Catalyst\Generator;

class ComposerSetupCommand extends Command
{
    use PhpVersionAskable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:composer
                            {php? : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the Dockerfile and justfile for the Composer project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $php = $this->getPhpVersionOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('composer', function () use ($php, $generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json);
            $json->write($definition);

            $generator
                ->replacePlaceHolder('__Php__', $php)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    /**
     * @return array<string, mixed>
     * @throws ParsingException
     */
    private function getNewDefinition(JsonFile $json): array
    {
        $definition = $json->read();

        if (! array_key_exists('scripts', $definition)) {
            $definition['scripts'] = [];
        }

        if (! array_key_exists('test', $definition['scripts'])) {
            $definition['scripts']['test'] = [
                "@php artisan config:clear --ansi",
                "@php artisan test",
            ];
        }

        if (! array_key_exists('coverage', $definition['scripts'])) {
            $definition['scripts']['coverage'] = [
                "@php artisan config:clear --ansi",
                "@php -dextension=pcov.so -d pcov.enabled=1 artisan test --coverage",
            ];
        }

        return $definition;
    }
}
