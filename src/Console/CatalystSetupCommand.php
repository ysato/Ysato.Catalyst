<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;

use function Laravel\Prompts\multiselect;

class CatalystSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the base architecture and install standards for a Laravel project.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = multiselect(
            label: 'What needs to be set up?',
            options: [
                'metadata' => 'Generates composer.json metadata.',
                'architecture-src' => 'Initializes recommended src architecture.',
                'phpcs' => 'Initializes PHP Code Sniffer configuration.',
                'phpmd' => 'Initializes PHP Mess Detector configuration.',
                'spectral' => 'Initializes Spectral (OpenAPI linter) configuration.',
                'github' => 'Sets up recommended GitHub workflows and rulesets.',
                'ide' => 'Initializes recommended IDE (e.g., PhpStorm) settings.',
                'act' => 'Configures local execution for GitHub Actions.',
            ],
            default: [
                'metadata',
                'architecture-src',
                'phpcs',
                'phpmd',
                'spectral',
                'github',
                'ide',
                'act',
            ],
            hint: 'Press the space key to select. (default: all)',
            required: true,
        );

        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        foreach ($permissions as $permission) {
            $this->components->task($permission, function () use ($permission, $vendor, $package) {
                match ($permission) {
                    'metadata' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
                    'architecture-src' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
                    'phpcs' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
                    'act' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
                    default => $this->callSilently("catalyst:$permission"),
                };
            });
        }

        return 0;
    }
}
