<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\NotificationSettingCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\NotificationSetting;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations\CreateNotificationSetting;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations\ListNotificationSettings;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations\UpdateNotificationSetting;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class NotificationSettingsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListNotificationSettings $listOperation = new ListNotificationSettings()): NotificationSettingCollection
    {
        $parser = new ResponseParser($this->client->getRaw('notification-settings', $listOperation));
        return NotificationSettingCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), NotificationSettingCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id): NotificationSetting
    {
        $parser = new ResponseParser($this->client->getRaw("notification-settings/{$id}"));
        return NotificationSetting::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function create(CreateNotificationSetting $createOperation): NotificationSetting
    {
        $parser = new ResponseParser($this->client->postRaw('notification-settings', $createOperation));
        return NotificationSetting::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function update(string $id, UpdateNotificationSetting $operation): NotificationSetting
    {
        $parser = new ResponseParser($this->client->patchRaw("notification-settings/{$id}", $operation));
        return NotificationSetting::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function delete(string $id): void
    {
        new ResponseParser($this->client->deleteRaw("notification-settings/{$id}"));
    }
}
