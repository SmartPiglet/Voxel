<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\SimulationScenarioType;
interface SimulationConfigCreate extends \JsonSerializable
{
    public static function getScenarioType(): SimulationScenarioType;
}
