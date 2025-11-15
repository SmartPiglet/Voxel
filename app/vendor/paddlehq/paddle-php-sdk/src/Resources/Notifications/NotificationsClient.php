<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Notifications;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\NotificationCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Notification;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Notifications\Operations\ListNotifications;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class NotificationsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListNotifications $listOperation = new ListNotifications()): NotificationCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/notifications', $listOperation));
        return NotificationCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), NotificationCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id): Notification
    {
        $parser = new ResponseParser($this->client->getRaw("/notifications/{$id}"));
        return Notification::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function replay(string $id): string
    {
        $parser = new ResponseParser($this->client->postRaw("/notifications/{$id}/replay"));
        $data = $parser->getData();
        return $data['notification_id'] ?? '';
    }
}
