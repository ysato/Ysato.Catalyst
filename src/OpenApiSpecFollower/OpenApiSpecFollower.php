<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\PathFinder;
use RuntimeException;

use function assert;
use function file_exists;
use function is_string;

class OpenApiSpecFollower
{
    private OpenApi $openApi;

    private static self|null $instance = null;

    private function __construct(
        string $openApiSpecPath,
        private Results $results = new Results(),
    ) {
        $this->openApi = $this->loadOpenApiSpec($openApiSpecPath);

        foreach ($this->getAllScenarios() as $scenario) {
            $this->results->addScenario($scenario);
        }
    }

    public static function create(string $openApiSpecPath): self
    {
        if (self::$instance === null) {
            self::$instance = new self($openApiSpecPath);
        }

        return self::$instance;
    }

    /** @psalm-api */
    public function followed(string $method, string $actualPath, string $statusCode): void
    {
        $abstractUri = $this->resolveToAbstractUri($method, $actualPath);

        $this->results->implemented($method, $abstractUri, $statusCode);
    }

    public function results(): Results
    {
        return $this->results;
    }

    protected function resolveToAbstractUri(string $method, string $actualUri): string
    {
        $finder = new PathFinder($this->openApi, $actualUri, $method);

        $addresses = $finder->search();

        return ! empty($addresses) ? $addresses[0]->path() : $actualUri;
    }

    protected function loadOpenApiSpec(string $openApiSpecPath): OpenApi
    {
        if (! file_exists($openApiSpecPath)) {
            throw new RuntimeException("OpenAPI specification file not found: {$openApiSpecPath}");
        }

        return Reader::readFromYamlFile($openApiSpecPath);
    }

    /** @return Scenario[] */
    protected function getAllScenarios(): array
    {
        $scenarios = [];
        foreach ($this->openApi->paths as $path => $pathItem) {
            assert(is_string($path));

            foreach ($pathItem->getOperations() as $method => $operation) {
                assert(is_string($method));
                if ($operation->responses === null) {
                    continue;
                }

                foreach ($operation->responses as $statusCode => $response) {
                    unset($response);
                    // @phpstan-ignore cast.string
                    $scenarios[] = new Scenario($path, $method, (string) $statusCode);
                }
            }
        }

        return $scenarios;
    }
}
