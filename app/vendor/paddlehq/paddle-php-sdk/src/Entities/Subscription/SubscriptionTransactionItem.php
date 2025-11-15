<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Subscription;

use Voxel\Vendor\Paddle\SDK\Entities\Price;
class SubscriptionTransactionItem
{
    private function __construct(public string $priceId, public Price $price, public int $quantity, public SubscriptionProration $proration)
    {
    }
}
