<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Event\EventTypeName;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\InvalidArgumentException;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Entity as NotificationEntity;
use Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\SimulationConfigCreate;
use Voxel\Vendor\Paddle\SDK\Undefined;
class CreateSimulation implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly string $notificationSettingId, public readonly EventTypeName|SimulationScenarioType $type, public readonly string $name, public readonly NotificationEntity|Undefined $payload = new Undefined(), public readonly SimulationConfigCreate|Undefined $config = new Undefined())
    {
        if ($config instanceof SimulationConfigCreate && !$config::getScenarioType()->equals($type)) {
            throw InvalidArgumentException::incompatibleArguments('config', 'type', (string) $type);
        }
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['notification_setting_id' => $this->notificationSettingId, 'type' => $this->type, 'name' => $this->name, 'payload' => $this->payload, 'config' => $this->config]);
    }
}
