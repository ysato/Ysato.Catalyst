<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Exception;
use Seld\JsonLint\ParsingException;
use Throwable;
use Ysato\Catalyst\Generator;

class StandardsSetupCommand extends BaseCommand
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:standards
                            {vendor=MyVendor : The vendor name (e.g.Acme) in camel case.}
                            {package=MyPackage : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up essential development standards and IDE configurations for your project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        try {
            $json = new JsonFile(Factory::getComposerFile());

            $definition = $json->read();

            $definition['scripts']['cs'] = 'phpcs';
            $definition['scripts']['cs-fix'] = 'phpcbf';
            $definition['scripts']['qa'] = 'phpmd src text ./phpmd.xml';
            $definition['scripts']['tests'] = ['@cs', '@qa', '@test'];

            $json->write($definition);

            $currentIgnore = $generator->getFilesystem()->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->replacePlaceHolder($vendor, $package)
                ->appendToFile('.gitignore', $currentIgnore)
                ->generate($this->laravel->basePath());
        } catch (ParsingException $e) {
            return $this->handleUserError($e);
        } catch (Exception $e) {
            return $this->handleSystemError($e);
        } catch (Throwable $e) {
            return $this->handleFatalError($e);
        }

        $this->info('Development standards configured successfully!');

        return 0;
    }
}