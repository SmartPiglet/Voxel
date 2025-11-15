<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\DiscountGroups;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\DiscountGroupCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\DiscountGroup;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\DiscountGroups\Operations\CreateDiscountGroup;
use Voxel\Vendor\Paddle\SDK\Resources\DiscountGroups\Operations\ListDiscountGroups;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class DiscountGroupsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListDiscountGroups $listOperation = new ListDiscountGroups()): DiscountGroupCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/discount-groups', $listOperation));
        return DiscountGroupCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), DiscountGroupCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function create(CreateDiscountGroup $createOperation): DiscountGroup
    {
        $parser = new ResponseParser($this->client->postRaw('/discount-groups', $createOperation));
        return DiscountGroup::from($parser->getData());
    }
}
