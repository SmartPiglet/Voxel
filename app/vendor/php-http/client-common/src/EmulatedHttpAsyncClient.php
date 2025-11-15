<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common;

use Voxel\Vendor\Http\Client\HttpAsyncClient;
use Voxel\Vendor\Http\Client\HttpClient;
use Voxel\Vendor\Psr\Http\Client\ClientInterface;
/**
 * Emulates an async HTTP client with the help of a synchronous client.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class EmulatedHttpAsyncClient implements HttpClient, HttpAsyncClient
{
    use HttpAsyncClientEmulator;
    use HttpClientDecorator;
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
