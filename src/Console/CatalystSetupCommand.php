<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
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
            $this->components->task($permission, function () use ($permission, $vendor, $package) {
                $this->callSilently("catalyst:$permission", compact('vendor', 'package'));
            });
        }

        return 0;
    }
}