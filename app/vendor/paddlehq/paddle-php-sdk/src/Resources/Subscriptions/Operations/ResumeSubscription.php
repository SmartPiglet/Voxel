<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\DateTime;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionOnResume;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionResumeEffectiveFrom;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class ResumeSubscription implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly SubscriptionResumeEffectiveFrom|\DateTimeInterface|null $effectiveFrom = null, public readonly SubscriptionOnResume|Undefined $onResume = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['effective_from' => $this->effectiveFrom instanceof \DateTimeInterface ? DateTime::from($this->effectiveFrom)?->format() : $this->effectiveFrom, 'on_resume' => $this->onResume]);
    }
}
