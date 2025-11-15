<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Subscription;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\AdjustmentItemTotals;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\AdjustmentProration;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\AdjustmentType;
class SubscriptionAdjustmentItem
{
    private function __construct(public string $itemId, public AdjustmentType $type, public string|null $amount, public AdjustmentProration $proration, public AdjustmentItemTotals $totals)
    {
    }
    public static function from(array $data): self
    {
        return new self(itemId: $data['item_id'], type: AdjustmentType::from($data['type']), amount: $data['amount'] ?? null, proration: AdjustmentProration::from($data['proration']), totals: AdjustmentItemTotals::from($data['totals']));
    }
}
