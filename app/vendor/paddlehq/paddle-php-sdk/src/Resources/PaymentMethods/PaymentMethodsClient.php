<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\PaymentMethods;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\PaymentMethodCollection;
use Voxel\Vendor\Paddle\SDK\Entities\PaymentMethod;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\PaymentMethods\Operations\ListPaymentMethods;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class PaymentMethodsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(string $customerId, ListPaymentMethods $listOperation = new ListPaymentMethods()): PaymentMethodCollection
    {
        $parser = new ResponseParser($this->client->getRaw("/customers/{$customerId}/payment-methods", $listOperation));
        return PaymentMethodCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), PaymentMethodCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $customerId, string $id): PaymentMethod
    {
        $parser = new ResponseParser($this->client->getRaw("/customers/{$customerId}/payment-methods/{$id}"));
        return PaymentMethod::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function delete(string $customerId, string $id): void
    {
        new ResponseParser($this->client->deleteRaw("/customers/{$customerId}/payment-methods/{$id}"));
    }
}
