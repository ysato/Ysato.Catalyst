<?php

declare(strict_types=1);

namespace Tests\Scaffold;

use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Ysato\Catalyst\Scaffold\FakeSandbox;
use Ysato\Catalyst\Scaffold\Input;
use Ysato\Catalyst\Scaffold\SandboxInterface;
use Ysato\Catalyst\Scaffold\Scaffolder;
use Ysato\Catalyst\Scaffold\Template\CaseFilters;
use Ysato\Catalyst\Scaffold\Template\Renderer;
use Ysato\Catalyst\Testing\MatchesSnapshots;

use function dirname;

use const DIRECTORY_SEPARATOR;

class ScaffolderTest extends TestCase
{
    use MatchesSnapshots;

    private TemporaryDirectory $temporaryDirectory;

    private SandboxInterface $sandbox;

    private Renderer $renderer;

    private string $stubPath;

    private string $basePath;

    public function setUp(): void
    {
        $this->stubPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'stubs';
        $this->basePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'project';
        $sandboxPath = __DIR__ . DIRECTORY_SEPARATOR . '__sandbox__';

        $this->temporaryDirectory = (new TemporaryDirectory())
            ->location($sandboxPath);
        $this->sandbox = new FakeSandbox($this->temporaryDirectory, $this->basePath);

        $loader = new FilesystemLoader($this->stubPath);
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new CaseFilters());

        $this->renderer = new Renderer($twig);
    }

    protected function tearDown(): void
    {
        $this->temporaryDirectory->delete();
    }

    /** @dataProvider inputProvider */
    public function testScaffold(string $vendor, string $package, string $php, string|null $caFilePath): void
    {
        $input = new Input($vendor, $package, $php, $caFilePath);

        $SUT = new Scaffolder($this->sandbox, $this->renderer, $this->stubPath, $this->basePath);

        $SUT->scaffold($input);

        $this->assertMatchesSnapshot($this->temporaryDirectory->path());
    }

    /** @return array<string, array<string|null>> */
    public static function inputProvider(): array
    {
        return [
            'basic' => ['vendor', 'package', '8.2', null],
            'with_ca' => ['vendor', 'package', '8.2', './certs/cert.pem'],
            'php83' => ['vendor', 'package', '8.3', null],
            'different_vendor' => ['acme', 'tool', '8.2', null],
        ];
    }
}
