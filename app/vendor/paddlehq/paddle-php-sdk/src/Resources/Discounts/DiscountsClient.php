<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Discounts;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\DiscountCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Discount;
use Voxel\Vendor\Paddle\SDK\Entities\Discount\DiscountStatus;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Discounts\Operations\CreateDiscount;
use Voxel\Vendor\Paddle\SDK\Resources\Discounts\Operations\GetDiscount;
use Voxel\Vendor\Paddle\SDK\Resources\Discounts\Operations\ListDiscounts;
use Voxel\Vendor\Paddle\SDK\Resources\Discounts\Operations\UpdateDiscount;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class DiscountsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListDiscounts $listOperation = new ListDiscounts()): DiscountCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/discounts', $listOperation));
        return DiscountCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), DiscountCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id, GetDiscount $operation = new GetDiscount()): Discount
    {
        $parser = new ResponseParser($this->client->getRaw("/discounts/{$id}", $operation));
        return Discount::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\DiscountApiError On a discount specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function create(CreateDiscount $createOperation): Discount
    {
        $parser = new ResponseParser($this->client->postRaw('/discounts', $createOperation));
        return Discount::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\DiscountApiError On a discount specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function update(string $id, UpdateDiscount $operation): Discount
    {
        $parser = new ResponseParser($this->client->patchRaw("/discounts/{$id}", $operation));
        return Discount::from($parser->getData());
    }
    /**
     * @throws ApiError                  On a generic API error
     * @throws ApiError\DiscountApiError On a discount specific API error
     * @throws MalformedResponse         If the API response was not parsable
     */
    public function archive(string $id): Discount
    {
        return $this->update($id, new UpdateDiscount(status: DiscountStatus::Archived()));
    }
}
