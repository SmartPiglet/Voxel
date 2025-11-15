<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Events;

use Voxel\Vendor\Paddle\SDK\Entities\Event;
use Voxel\Vendor\Paddle\SDK\Entities\Event\EventTypeName;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Entity;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Report;
final class ReportUpdated extends Event
{
    private function __construct(string $eventId, EventTypeName $eventType, \DateTimeInterface $occurredAt, public readonly Report $report, string|null $notificationId)
    {
        parent::__construct($eventId, $eventType, $occurredAt, $report, $notificationId);
    }
    /**
     * @param Report $data
     */
    public static function fromEvent(string $eventId, EventTypeName $eventType, \DateTimeInterface $occurredAt, Entity $data, string|null $notificationId = null): static
    {
        return new self($eventId, $eventType, $occurredAt, $data, $notificationId);
    }
}
