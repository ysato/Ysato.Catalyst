<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Exception;
use Illuminate\Support\Str;
use Seld\JsonLint\ParsingException;
use Throwable;

class MetadataSetupCommand extends BaseCommand
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:metadata
                            {vendor=MyVendor : The vendor name (e.g.Acme) in camel case.}
                            {package=MyPackage : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Configures the project's metadata.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $json = new JsonFile(Factory::getComposerFile());

        try {
            $definition = $json->read();

            unset(
                $definition['keywords'],
                $definition['homepage'],
                $definition['description'],
            );

            $definition['name'] = sprintf('%s/%s', Str::kebab($vendor), Str::kebab($package));
            $definition['license'] = 'proprietary';

            $json->write($definition);
        } catch (ParsingException $e) {
            return $this->handleUserError($e);
        } catch (Exception $e) {
            return $this->handleSystemError($e);
        } catch (Throwable $e) {
            return $this->handleFatalError($e);
        }

        $this->info('Project metadata configured successfully!');

        return 0;
    }
}