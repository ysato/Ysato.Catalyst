<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Exception;
use Seld\JsonLint\ParsingException;
use Throwable;
use Ysato\Catalyst\Generator;

class ArchitectureSrcSetupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:architecture-src
                            {vendor=MyVendor : The vendor name (e.g.Acme) in camel case.}
                            {package=MyPackage : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the base architecture for a Laravel project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator): int
    {
        $vendor = $this->argument('vendor') ?? $this->askVendorName();
        $package = $this->argument('package') ?? $this->askPackageName();

        try {
            $json = new JsonFile(Factory::getComposerFile());

            $definition = $json->read();

            $definition['autoload']['psr-4']["$vendor\\$package\\"] = 'src/';

            $json->write($definition);

            $generator
                ->replacePlaceHolder($vendor, $package)
                ->generate($this->laravel->basePath());
        } catch (ParsingException $e) {
            return $this->handleUserError($e);
        } catch (Exception $e) {
            return $this->handleSystemError($e);
        } catch (Throwable $e) {
            return $this->handleFatalError($e);
        }

        $this->info('Src architecture setup complete!');

        return 0;
    }
}