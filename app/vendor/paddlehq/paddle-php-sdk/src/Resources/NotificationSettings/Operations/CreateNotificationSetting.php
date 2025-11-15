<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Event\EventTypeName;
use Voxel\Vendor\Paddle\SDK\Entities\NotificationSetting\NotificationSettingType;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class CreateNotificationSetting implements \JsonSerializable
{
    use FiltersUndefined;
    /**
     * @param EventTypeName[] $subscribedEvents
     */
    public function __construct(public readonly string $description, public readonly string $destination, public readonly array $subscribedEvents, public readonly NotificationSettingType $type, public readonly bool $includeSensitiveFields, public readonly int|Undefined $apiVersion = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['description' => $this->description, 'destination' => $this->destination, 'subscribed_events' => $this->subscribedEvents, 'type' => $this->type, 'include_sensitive_fields' => $this->includeSensitiveFields, 'api_version' => $this->apiVersion]);
    }
}
