<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Events;

use Voxel\Vendor\Paddle\SDK\Entities\Event;
use Voxel\Vendor\Paddle\SDK\Entities\Event\EventTypeName;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\DiscountGroup;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Entity;
final class DiscountGroupCreated extends Event
{
    private function __construct(string $eventId, EventTypeName $eventType, \DateTimeInterface $occurredAt, public readonly DiscountGroup $discountGroup, string|null $notificationId)
    {
        parent::__construct($eventId, $eventType, $occurredAt, $discountGroup, $notificationId);
    }
    /**
     * @param DiscountGroup $data
     */
    public static function fromEvent(string $eventId, EventTypeName $eventType, \DateTimeInterface $occurredAt, Entity $data, string|null $notificationId = null): static
    {
        return new self($eventId, $eventType, $occurredAt, $data, $notificationId);
    }
}
