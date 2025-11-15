<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Businesses;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Business;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\BusinessCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Status;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Businesses\Operations\CreateBusiness;
use Voxel\Vendor\Paddle\SDK\Resources\Businesses\Operations\ListBusinesses;
use Voxel\Vendor\Paddle\SDK\Resources\Businesses\Operations\UpdateBusiness;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class BusinessesClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(string $customerId, ListBusinesses $listOperation = new ListBusinesses()): BusinessCollection
    {
        $parser = new ResponseParser($this->client->getRaw("/customers/{$customerId}/businesses", $listOperation));
        return BusinessCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), BusinessCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $customerId, string $id): Business
    {
        $parser = new ResponseParser($this->client->getRaw("/customers/{$customerId}/businesses/{$id}"));
        return Business::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\BusinessApiError On an business specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function create(string $customerId, CreateBusiness $createOperation): Business
    {
        $parser = new ResponseParser($this->client->postRaw("/customers/{$customerId}/businesses", $createOperation));
        return Business::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\BusinessApiError On an business specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function update(string $customerId, string $id, UpdateBusiness $operation): Business
    {
        $parser = new ResponseParser($this->client->patchRaw("/customers/{$customerId}/businesses/{$id}", $operation));
        return Business::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\BusinessApiError On an business specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function archive(string $customerId, string $id): Business
    {
        return $this->update($customerId, $id, new UpdateBusiness(status: Status::Archived()));
    }
}
