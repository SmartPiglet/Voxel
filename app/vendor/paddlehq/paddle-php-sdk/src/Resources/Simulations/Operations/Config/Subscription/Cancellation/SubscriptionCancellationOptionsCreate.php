<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Cancellation;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Option\EffectiveFrom;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class SubscriptionCancellationOptionsCreate implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly EffectiveFrom|Undefined $effectiveFrom = new Undefined(), public readonly bool|Undefined $hasPastDueTransaction = new Undefined())
    {
    }
    public function jsonSerialize(): \stdClass
    {
        return (object) $this->filterUndefined(['effective_from' => $this->effectiveFrom, 'has_past_due_transaction' => $this->hasPastDueTransaction]);
    }
}
