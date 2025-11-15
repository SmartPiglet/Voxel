<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Creation;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Option\BusinessSimulatedAs;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Option\CustomerSimulatedAs;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Option\DiscountSimulatedAs;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class SubscriptionCreationOptionsCreate implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly CustomerSimulatedAs|Undefined $customerSimulatedAs = new Undefined(), public readonly BusinessSimulatedAs|Undefined $businessSimulatedAs = new Undefined(), public readonly DiscountSimulatedAs|Undefined $discountSimulatedAs = new Undefined())
    {
    }
    public function jsonSerialize(): \stdClass
    {
        return (object) $this->filterUndefined(['customer_simulated_as' => $this->customerSimulatedAs, 'business_simulated_as' => $this->businessSimulatedAs, 'discount_simulated_as' => $this->discountSimulatedAs]);
    }
}
