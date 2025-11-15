<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Logger;

use Voxel\Vendor\Psr\Http\Message\RequestInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseInterface;
final class Formatter implements \Voxel\Vendor\Http\Message\Formatter
{
    public function formatRequest(RequestInterface $request): string
    {
        return sprintf('%s %s %s %s', $request->getMethod(), $request->getUri()->__toString(), $request->getProtocolVersion(), $request->getHeaderLine('X-Transaction-ID') ?: '-');
    }
    public function formatResponse(ResponseInterface $response): string
    {
        return sprintf('%s %s %s -', $response->getStatusCode(), $response->getReasonPhrase(), $response->getProtocolVersion());
    }
    public function formatResponseForRequest(ResponseInterface $response, RequestInterface $request): string
    {
        return sprintf('%s %s %s %s', $response->getStatusCode(), $response->getReasonPhrase(), $response->getProtocolVersion(), $request->getHeaderLine('X-Transaction-ID') ?: '-');
    }
}
