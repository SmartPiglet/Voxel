<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Creation;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\SimulationConfigCreate;
use Voxel\Vendor\Paddle\SDK\Undefined;
class SubscriptionCreationConfigCreate implements SimulationConfigCreate
{
    use FiltersUndefined;
    public function __construct(public readonly SubscriptionCreationEntitiesCreate|Undefined $entities = new Undefined(), public readonly SubscriptionCreationOptionsCreate|Undefined $options = new Undefined())
    {
    }
    public static function getScenarioType(): SimulationScenarioType
    {
        return SimulationScenarioType::SubscriptionCreation();
    }
    public function jsonSerialize(): array
    {
        return ['subscription_creation' => (object) $this->filterUndefined(['entities' => $this->entities, 'options' => $this->options])];
    }
}
