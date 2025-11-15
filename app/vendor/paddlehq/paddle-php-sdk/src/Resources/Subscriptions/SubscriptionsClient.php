<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Subscriptions;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\SubscriptionCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription;
use Voxel\Vendor\Paddle\SDK\Entities\SubscriptionPreview;
use Voxel\Vendor\Paddle\SDK\Entities\Transaction;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\InvalidArgumentException;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\CreateOneTimeCharge;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\Get\Includes;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\ListSubscriptions;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\PauseSubscription;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\PreviewOneTimeCharge;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\PreviewUpdateSubscription;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\ResumeSubscription;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\UpdateSubscription;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class SubscriptionsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListSubscriptions $listOperation = new ListSubscriptions()): SubscriptionCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/subscriptions', $listOperation));
        return SubscriptionCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), SubscriptionCollection::class));
    }
    /**
     * @param Includes[] $includes
     *
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id, array $includes = []): Subscription
    {
        if ($invalid = array_filter($includes, fn($value): bool => !$value instanceof Includes)) {
            throw InvalidArgumentException::arrayContainsInvalidTypes('includes', Includes::class, implode(', ', $invalid));
        }
        $params = $includes === [] ? [] : ['include' => implode(',', array_map(fn($enum) => $enum->getValue(), $includes))];
        $parser = new ResponseParser($this->client->getRaw("/subscriptions/{$id}", $params));
        return Subscription::from($parser->getData());
    }
    /**
     * @throws ApiError                      On a generic API error
     * @throws ApiError\SubscriptionApiError On a subscription specific API error
     * @throws MalformedResponse             If the API response was not parsable
     */
    public function update(string $id, UpdateSubscription $operation): Subscription
    {
        $parser = new ResponseParser($this->client->patchRaw("/subscriptions/{$id}", $operation));
        return Subscription::from($parser->getData());
    }
    public function pause(string $id, PauseSubscription $operation): Subscription
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/pause", $operation));
        return Subscription::from($parser->getData());
    }
    public function resume(string $id, ResumeSubscription $operation): Subscription
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/resume", $operation));
        return Subscription::from($parser->getData());
    }
    public function cancel(string $id, CancelSubscription $operation): Subscription
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/cancel", $operation));
        return Subscription::from($parser->getData());
    }
    public function getPaymentMethodChangeTransaction(string $id): Transaction
    {
        $parser = new ResponseParser($this->client->getRaw("/subscriptions/{$id}/update-payment-method-transaction"));
        return Transaction::from($parser->getData());
    }
    public function activate(string $id): Subscription
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/activate"));
        return Subscription::from($parser->getData());
    }
    public function createOneTimeCharge(string $id, CreateOneTimeCharge $operation): Subscription
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/charge", $operation));
        return Subscription::from($parser->getData());
    }
    public function previewUpdate(string $id, PreviewUpdateSubscription $operation): SubscriptionPreview
    {
        $parser = new ResponseParser($this->client->patchRaw("/subscriptions/{$id}/preview", $operation));
        return SubscriptionPreview::from($parser->getData());
    }
    public function previewOneTimeCharge(string $id, PreviewOneTimeCharge $operation): SubscriptionPreview
    {
        $parser = new ResponseParser($this->client->postRaw("/subscriptions/{$id}/charge/preview", $operation));
        return SubscriptionPreview::from($parser->getData());
    }
}
