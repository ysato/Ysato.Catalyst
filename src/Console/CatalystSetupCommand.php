<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use function Laravel\Prompts\multiselect;

class CatalystSetupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup';

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
            hint: 'Press the space key to select.'
        );

        $vendor = $this->askVendorName();
        $package = $this->askPackageName();

        foreach ($permissions as $permission) {
            match ($permission) {
                'metadata' => $this->call('catalyst:metadata', compact('vendor', 'package')),
                'architecture-src' => $this->call('catalyst:architecture-src', compact('vendor', 'package')),
                'standards' => $this->call('catalyst:standards', compact('vendor', 'package')),
            };
        }

        $this->info('The setup completed successfully.');

        return 0;
    }
}