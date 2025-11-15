<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities;

use Voxel\Vendor\Paddle\SDK\Entities\EventNameResolver;
class EntityFactory
{
    public static function create(string $eventType, array $data): Entity
    {
        return self::resolveEntityClass($eventType)::from($data);
    }
    /**
     * @return class-string<Entity>
     */
    private static function resolveEntityClass(string $eventType): string
    {
        /** @var class-string<Entity> $entity */
        $entity = EntityNameResolver::resolveFqn($eventType);
        if ($entity === UndefinedEntity::class) {
            return $entity;
        }
        if (!class_exists($entity) || !in_array(Entity::class, class_implements($entity), \true)) {
            $identifier = EventNameResolver::resolve($eventType);
            throw new \UnexpectedValueException("Event type '{$identifier}' cannot be mapped to an object");
        }
        return $entity;
    }
}
