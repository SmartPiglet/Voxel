<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Prices\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CatalogType;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Money;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\PriceQuantity;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxMode;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TimePeriod;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\UnitPriceOverride;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class CreatePrice implements \JsonSerializable
{
    use FiltersUndefined;
    /**
     * @param UnitPriceOverride[] $unitPriceOverrides
     */
    public function __construct(public readonly string $description, public readonly string $productId, public readonly Money $unitPrice, public readonly string|Undefined|null $name = new Undefined(), public readonly CatalogType|Undefined|null $type = new Undefined(), public readonly array|Undefined $unitPriceOverrides = new Undefined(), public readonly TaxMode|Undefined $taxMode = new Undefined(), public readonly TimePeriod|Undefined|null $trialPeriod = new Undefined(), public readonly TimePeriod|Undefined|null $billingCycle = new Undefined(), public readonly PriceQuantity|Undefined $quantity = new Undefined(), public readonly CustomData|Undefined $customData = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['description' => $this->description, 'product_id' => $this->productId, 'unit_price' => $this->unitPrice, 'name' => $this->name, 'type' => $this->type, 'unit_price_overrides' => $this->unitPriceOverrides, 'trial_period' => $this->trialPeriod, 'billing_cycle' => $this->billingCycle, 'custom_data' => $this->customData, 'tax_mode' => $this->taxMode, 'quantity' => $this->quantity]);
    }
}
