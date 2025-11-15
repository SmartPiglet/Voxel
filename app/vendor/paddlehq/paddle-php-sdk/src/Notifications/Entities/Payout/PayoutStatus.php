<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities\Payout;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static PayoutStatus Unpaid()
 * @method static PayoutStatus Paid()
 */
final class PayoutStatus extends PaddleEnum
{
    private const Unpaid = 'unpaid';
    private const Paid = 'paid';
}
