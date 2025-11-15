<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Pause;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\SimulationConfigCreate;
use Voxel\Vendor\Paddle\SDK\Undefined;
class SubscriptionPauseConfigCreate implements SimulationConfigCreate
{
    use FiltersUndefined;
    public function __construct(public readonly SubscriptionPauseEntitiesCreate|Undefined $entities = new Undefined(), public readonly SubscriptionPauseOptionsCreate|Undefined $options = new Undefined())
    {
    }
    public static function getScenarioType(): SimulationScenarioType
    {
        return SimulationScenarioType::SubscriptionPause();
    }
    public function jsonSerialize(): array
    {
        return ['subscription_pause' => (object) $this->filterUndefined(['entities' => $this->entities, 'options' => $this->options])];
    }
}
