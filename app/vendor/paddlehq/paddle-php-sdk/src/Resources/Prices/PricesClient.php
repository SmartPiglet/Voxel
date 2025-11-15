<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Prices;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\PriceCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Price;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Status;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\InvalidArgumentException;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Prices\Operations\CreatePrice;
use Voxel\Vendor\Paddle\SDK\Resources\Prices\Operations\List\Includes;
use Voxel\Vendor\Paddle\SDK\Resources\Prices\Operations\ListPrices;
use Voxel\Vendor\Paddle\SDK\Resources\Prices\Operations\UpdatePrice;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class PricesClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListPrices $listOperation = new ListPrices()): PriceCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/prices', $listOperation));
        return PriceCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), PriceCollection::class));
    }
    /**
     * @param array<Includes> $includes
     *
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id, array $includes = []): Price
    {
        if ($invalid = array_filter($includes, fn($value): bool => !$value instanceof Includes)) {
            throw InvalidArgumentException::arrayContainsInvalidTypes('includes', Includes::class, implode(', ', $invalid));
        }
        $params = $includes === [] ? [] : ['include' => implode(',', array_map(fn($enum) => $enum->getValue(), $includes))];
        $parser = new ResponseParser($this->client->getRaw("/prices/{$id}", $params));
        return Price::from($parser->getData());
    }
    /**
     * @throws ApiError               On a generic API error
     * @throws ApiError\PriceApiError On a price specific API error
     * @throws MalformedResponse      If the API response was not parsable
     */
    public function create(CreatePrice $createOperation): Price
    {
        $parser = new ResponseParser($this->client->postRaw('/prices', $createOperation));
        return Price::from($parser->getData());
    }
    /**
     * @throws ApiError               On a generic API error
     * @throws ApiError\PriceApiError On a price specific API error
     * @throws MalformedResponse      If the API response was not parsable
     */
    public function update(string $id, UpdatePrice $operation): Price
    {
        $parser = new ResponseParser($this->client->patchRaw("/prices/{$id}", $operation));
        return Price::from($parser->getData());
    }
    /**
     * @throws ApiError               On a generic API error
     * @throws ApiError\PriceApiError On a price specific API error
     * @throws MalformedResponse      If the API response was not parsable
     */
    public function archive(string $id): Price
    {
        return $this->update($id, new UpdatePrice(status: Status::Archived()));
    }
}
