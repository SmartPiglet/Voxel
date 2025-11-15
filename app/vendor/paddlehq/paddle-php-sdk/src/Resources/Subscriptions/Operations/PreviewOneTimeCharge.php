<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionItems;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionItemsWithPrice;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionOnPaymentFailure;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class PreviewOneTimeCharge implements \JsonSerializable
{
    use FiltersUndefined;
    /**
     * @param array<SubscriptionItems|SubscriptionItemsWithPrice> $items
     */
    public function __construct(public readonly SubscriptionEffectiveFrom $effectiveFrom, public readonly array $items, public readonly SubscriptionOnPaymentFailure|Undefined $onPaymentFailure = new Undefined(), public readonly string|Undefined $receiptData = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['effective_from' => $this->effectiveFrom, 'items' => $this->items, 'on_payment_failure' => $this->onPaymentFailure, 'receipt_data' => $this->receiptData]);
    }
}
