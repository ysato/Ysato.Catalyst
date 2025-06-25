<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use League\OpenAPIValidation\PSR7\Exception\Validation\AddressValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

use function strtolower;

/** @phpstan-ignore trait.unused */
trait ValidatesOpenApiSpec
{
    use \Kirschbaum\OpenApiValidator\ValidatesOpenApiSpec;

    protected function validateRequest(SymfonyRequest $request): OperationAddress
    {
        if ($this->shouldSkipRequestValidation()) {
            $psr7Request = $this->getPsr7Factory()->createRequest($request);

            return new OperationAddress($psr7Request->getUri()->getPath(), strtolower($request->getMethod()));
        }

        $authenticatedRequest = $this->getAuthenticatedRequest($request);

        try {
            return $this->getOpenApiValidatorBuilder()
                ->getRequestValidator()
                ->validate($this->getPsr7Factory()->createRequest($authenticatedRequest));
        } catch (AddressValidationFailed $e) {
            $this->handleAddressValidationFailed($e, $request->getContent());
        }
    }
}
