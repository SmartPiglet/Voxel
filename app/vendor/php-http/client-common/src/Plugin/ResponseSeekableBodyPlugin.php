<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common\Plugin;

use Voxel\Vendor\Http\Message\Stream\BufferedStream;
use Voxel\Vendor\Http\Promise\Promise;
use Voxel\Vendor\Psr\Http\Message\RequestInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * Allow body used in response to be always seekable.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class ResponseSeekableBodyPlugin extends SeekableBodyPlugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($request)->then(function (ResponseInterface $response) {
            if ($response->getBody()->isSeekable()) {
                return $response;
            }
            return $response->withBody(new BufferedStream($response->getBody(), $this->useFileBuffer, $this->memoryBufferSize));
        });
    }
}
