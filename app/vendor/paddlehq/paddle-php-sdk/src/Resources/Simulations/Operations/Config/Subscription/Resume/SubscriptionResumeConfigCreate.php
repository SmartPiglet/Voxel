<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Resume;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\SimulationConfigCreate;
use Voxel\Vendor\Paddle\SDK\Undefined;
class SubscriptionResumeConfigCreate implements SimulationConfigCreate
{
    use FiltersUndefined;
    public function __construct(public readonly SubscriptionResumeEntitiesCreate|Undefined $entities = new Undefined(), public readonly SubscriptionResumeOptionsCreate|Undefined $options = new Undefined())
    {
    }
    public static function getScenarioType(): SimulationScenarioType
    {
        return SimulationScenarioType::SubscriptionResume();
    }
    public function jsonSerialize(): array
    {
        return ['subscription_resume' => (object) $this->filterUndefined(['entities' => $this->entities, 'options' => $this->options])];
    }
}
