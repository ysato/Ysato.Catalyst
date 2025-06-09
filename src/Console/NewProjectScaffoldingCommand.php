<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Ysato\Catalyst\Console\Concerns\PhpVersionAskable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;

class NewProjectScaffoldingCommand extends Command
{
    use VendorPackageAskableTrait;
    use PhpVersionAskable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}
                            {php? : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To automatically generate and configure all necessary components for a new project.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();
        $php = $this->getPhpVersionOrAsk();

        unset($php);

        $workflow = [
            'catalyst:scaffold-core-structure:generate-composer-metadata',
            'catalyst:scaffold-core-structure:initialize-directory-architecture',
        ];

        foreach ($workflow as $command) {
            match (Str::between($command, ':', ':')) {
                'scaffold-core-structure' => $this->runScaffoldCoreStructure($command, $vendor, $package),
            };
        }

//        foreach ($permissions as $permission) {
//            $this->components->task($permission, function () use ($permission, $vendor, $package, $php) {
//                match ($permission) {
//                    'metadata' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
//                    'architecture-src' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
//                    'phpcs' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
//                    'github' => $this->callSilently("catalyst:$permission", compact('php')),
//                    'act' => $this->callSilently("catalyst:$permission", compact('vendor', 'package')),
//                    'composer' => $this->callSilently("catalyst:$permission", compact('php')),
//                    default => $this->callSilently("catalyst:$permission"),
//                };
//            });
//        }

        return 0;
    }

    private function runScaffoldCoreStructure(string $command, string $vendor, string $package): void
    {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'generate-composer-metadata' => $this->call($command, compact('vendor', 'package')),
        };
    }
}
