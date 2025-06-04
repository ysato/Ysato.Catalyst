<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use function Laravel\Prompts\multiselect;

class CatalystSetupCommand extends BaseCommand
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup
                            {vendor=MyVendor : The vendor name (e.g.Acme) in camel case.}
                            {package=MyPackage : The package name (e.g.Blog) in camel case.}';

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
                'metadata' => 'composer.json metadata',
                'architecture-src' => 'src architecture',
                'standards' => 'qa and style standards',
            ],
            default: ['metadata', 'architecture-src', 'standards'],
            hint: 'Press the space key to select. (default: all)',
            required: true,
        );

        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        foreach ($permissions as $permission) {
            $exitCode = match ($permission) {
                'metadata' => $this->callSilently('catalyst:metadata', compact('vendor', 'package')),
                'architecture-src' => $this->callSilently('catalyst:architecture-src', compact('vendor', 'package')),
                'standards' => $this->callSilently('catalyst:standards', compact('vendor', 'package')),
            };

            if ($exitCode !== 0) {
                $this->error("Failed to set up: {$permission}");

                return $exitCode;
            }
        }

        $this->info('The setup completed successfully!');

        return 0;
    }
}