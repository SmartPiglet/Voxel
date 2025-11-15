<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\NotificationLogs;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\NotificationLogCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationLogs\Operations\ListNotificationLogs;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class NotificationLogsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(string $notificationId, ListNotificationLogs $listOperation = new ListNotificationLogs()): NotificationLogCollection
    {
        $parser = new ResponseParser($this->client->getRaw("/notifications/{$notificationId}/logs", $listOperation));
        return NotificationLogCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), NotificationLogCollection::class));
    }
}
