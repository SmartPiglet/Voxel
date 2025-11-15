<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Simulations\Operations\Config\Subscription\Creation;

use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
class SubscriptionCreationItemCreate implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly string $priceId, public readonly int $quantity)
    {
    }
    public function jsonSerialize(): array
    {
        return ['price_id' => $this->priceId, 'quantity' => $this->quantity];
    }
}
