<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\CustomerPortalSessions;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\CustomerPortalSession;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\CustomerPortalSessions\Operations\CreateCustomerPortalSession;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class CustomerPortalSessionsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function create(string $customerId, CreateCustomerPortalSession $createOperation): CustomerPortalSession
    {
        $parser = new ResponseParser($this->client->postRaw("/customers/{$customerId}/portal-sessions", $createOperation));
        return CustomerPortalSession::from($parser->getData());
    }
}
