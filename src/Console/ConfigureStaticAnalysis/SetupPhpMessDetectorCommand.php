<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ConfigureStaticAnalysis;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class SetupPhpMessDetectorCommand extends Command
{
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:configure-static-analysis:setup-php-mess-detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup PHPMD';

    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json);
            $json->write($definition);

            $generator->generate($this->laravel->basePath());
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

        if (! array_key_exists('qa', $definition['scripts'])) {
            $definition['scripts']['qa'] = [];
        }

        $washed = preg_grep('/^@?phpmd/', $definition['scripts']['qa'], PREG_GREP_INVERT);
        $washed[] = 'phpmd src text ./phpmd.xml';
        $definition['scripts']['qa'] = $washed;

        if (! array_key_exists('tests', $definition['scripts'])) {
            $definition['scripts']['tests'] = [];
        }

        if (! in_array('@qa', $definition['scripts']['tests'], true)) {
            $definition['scripts']['tests'][] = '@qa';
        }

        return $definition;
    }
}
