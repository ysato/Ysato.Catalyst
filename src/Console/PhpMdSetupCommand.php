<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Generator;

class PhpMdSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:phpmd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes PHP Mess Detector by setting up configuration files and recommended rule sets for the project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('phpmd', function () use ($generator) {
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

        $qas = Arr::get($definition, 'scripts.qa', []);
        if (! in_array('phpmd src text ./phpmd.xml', $qas, true)) {
            $definition['scripts']['qa'][] = 'phpmd src text ./phpmd.xml';
        }

        $tests = Arr::get($definition, 'scripts.tests', []);
        if (! in_array('@qa', $tests, true)) {
            $definition['scripts']['tests'][] = '@qa';
        }

        return $definition;
    }
}