<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\NotificationSetting\NotificationSettingTrafficSource;
use Voxel\Vendor\Paddle\SDK\HasParameters;
use Voxel\Vendor\Paddle\SDK\Resources\Shared\Operations\List\Pager;
class ListNotificationSettings implements HasParameters
{
    public function __construct(private readonly Pager|null $pager = null, private readonly bool|null $active = null, private readonly NotificationSettingTrafficSource|null $trafficSource = null)
    {
    }
    public function getParameters(): array
    {
        return array_merge($this->pager?->getParameters() ?? [], array_filter(['active' => isset($this->active) ? $this->active ? 'true' : 'false' : null, 'traffic_source' => $this->trafficSource?->getValue()]));
    }
}
