<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation;

use Voxel\Vendor\Paddle\SDK\Notifications\Entities\EntityNameResolver;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\UndefinedEntity;
/**
 * @internal
 */
final class SimulationEntityNameResolver
{
    public static function resolve(string $eventType): string
    {
        return EntityNameResolver::resolve($eventType);
    }
    public static function resolveFqn(string $eventType): string
    {
        $fqn = sprintf('\Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation\%s', self::resolve($eventType));
        return class_exists($fqn) ? $fqn : UndefinedEntity::class;
    }
}
