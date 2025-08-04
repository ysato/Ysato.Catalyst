<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower;

use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

/** @phpstan-ignore trait.unused */
trait FollowsOpenApiSpec
{
    use OpenApiSpecPath;

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
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);

        $this->followed($method, $uri, $response->getStatusCode());

        return $response;
    }

    protected function followed(string $method, string $uri, int $statusCode): void
    {
        $follower = OpenApiSpecFollower::create($this->getOpenApiSpecPath());
        $follower->followed($method, $uri, (string) $statusCode);
    }
}
