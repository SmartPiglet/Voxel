<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Subscription;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Money;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\PriceQuantity;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxMode;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TimePeriod;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\UnitPriceOverride;
class SubscriptionNonCatalogPrice
{
    /**
     * @param array<UnitPriceOverride> $unitPriceOverrides
     */
    public function __construct(public string $description, public string|null $name, public string $productId, public TaxMode $taxMode, public Money $unitPrice, public array $unitPriceOverrides, public PriceQuantity $quantity, public CustomData|null $customData, public TimePeriod|null $billingCycle = null, public TimePeriod|null $trialPeriod = null)
    {
    }
}
