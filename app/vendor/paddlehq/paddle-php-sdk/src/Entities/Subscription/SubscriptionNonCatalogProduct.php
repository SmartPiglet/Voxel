<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Subscription;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CatalogType;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxCategory;
class SubscriptionNonCatalogProduct
{
    public function __construct(public string $name, public string|null $description, public CatalogType|null $type, public TaxCategory $taxCategory, public string|null $imageUrl, public CustomData|null $customData)
    {
    }
}
