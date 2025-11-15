<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities;

use Voxel\Vendor\Paddle\SDK\Entities\Event\EventTypeName;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\SimulationConfig;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationStatus;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Entity as NotificationEntity;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation\SimulationEntity;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation\SimulationEntityFactory;
class Simulation implements Entity
{
    private function __construct(public string $id, public SimulationStatus $status, public string $notificationSettingId, public string $name, public EventTypeName|SimulationScenarioType $type, public NotificationEntity|SimulationEntity|null $payload, public SimulationConfig|null $config, public \DateTimeInterface|null $lastRunAt, public \DateTimeInterface $createdAt, public \DateTimeInterface $updatedAt)
    {
    }
    public static function from(array $data): self
    {
        return new self(id: $data['id'], status: SimulationStatus::from($data['status']), notificationSettingId: $data['notification_setting_id'], name: $data['name'], type: EventTypeName::from($data['type'])->isKnown() ? EventTypeName::from($data['type']) : SimulationScenarioType::from($data['type']), payload: $data['payload'] ? SimulationEntityFactory::create($data['type'], $data['payload']) : null, config: isset($data['config']) ? SimulationConfig::from($data['config']) : null, lastRunAt: isset($data['last_run_at']) ? DateTime::from($data['last_run_at']) : null, createdAt: DateTime::from($data['created_at']), updatedAt: DateTime::from($data['updated_at']));
    }
}
