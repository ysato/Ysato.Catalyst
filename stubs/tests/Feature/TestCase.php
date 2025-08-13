<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Kirschbaum\OpenApiValidator\ValidatesOpenApiSpec;
use Override;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Ysato\Spectator\Spectatable;

use function array_merge;
use function array_replace;

abstract class TestCase extends \Tests\TestCase
{
    use RefreshDatabase;
    use ValidatesOpenApiSpec;
    use Spectatable {
        Spectatable::getOpenApiSpecPath insteadof ValidatesOpenApiSpec;
    }

    /**
     * @param string                  $method
     * @param string                  $uri
     * @param array<array-key, mixed> $parameters
     * @param array<array-key, mixed> $cookies
     * @param array<array-key, mixed> $files
     * @param array<array-key, mixed> $server
     * @param string|null             $content
     *
     * @return TestResponse<Response>
     *
     * @throws BindingResolutionException
     */
    #[Override]
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $kernel = $this->app->make(HttpKernel::class);

        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));

        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content,
        );

        $request = Request::createFromBase($symfonyRequest);

        $address = $this->validateRequest($request);

        $response = $kernel->handle($request);

        if ($this->followRedirects) {
            /** @phpstan-var \Illuminate\Http\Response $response */
            $response = $this->followRedirects($response);
        }

        $kernel->terminate($request, $response);

        $testResponse = $this->createTestResponse($response, $request);

        $this->validateResponse($address, $testResponse->baseResponse);

        $this->spectate($method, $uri, $testResponse->getStatusCode());

        return $testResponse;
    }
}
