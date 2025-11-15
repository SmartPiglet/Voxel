<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Collections;

use Voxel\Vendor\Paddle\SDK\Entities\Subscription;
class SubscriptionCollection extends Collection
{
    public static function from(array $itemsData, Paginator|null $paginator = null): self
    {
        return new self(array_map(fn(array $item): Subscription => Subscription::from($item), $itemsData), $paginator);
    }
    public function current(): Subscription
    {
        return parent::current();
    }
}
