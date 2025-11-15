<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities;

/**
 * @internal
 */
final class EventNameResolver
{
    public static function resolve(string $eventType): string
    {
        $type = explode('.', $eventType);
        return str_replace('_', '', ucwords(implode('_', $type), '_'));
    }
}
