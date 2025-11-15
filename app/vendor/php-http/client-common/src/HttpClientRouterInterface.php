<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common;

use Voxel\Vendor\Http\Client\HttpAsyncClient;
use Voxel\Vendor\Http\Client\HttpClient;
use Voxel\Vendor\Http\Message\RequestMatcher;
use Voxel\Vendor\Psr\Http\Client\ClientInterface;
/**
 * Route a request to a specific client in the stack based using a RequestMatcher.
 *
 * This is not a HttpClientPool client because it uses a matcher to select the client.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
interface HttpClientRouterInterface extends HttpClient, HttpAsyncClient
{
    /**
     * Add a client to the router.
     *
     * @param ClientInterface|HttpAsyncClient $client
     */
    public function addClient($client, RequestMatcher $requestMatcher): void;
}
