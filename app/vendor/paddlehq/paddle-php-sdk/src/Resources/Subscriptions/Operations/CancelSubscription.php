<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
class CancelSubscription implements \JsonSerializable
{
    public function __construct(public readonly SubscriptionEffectiveFrom|null $effectiveFrom = null)
    {
    }
    public function jsonSerialize(): array
    {
        return ['effective_from' => $this->effectiveFrom];
    }
}
