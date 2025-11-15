<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities\Discount;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static DiscountType Flat()
 * @method static DiscountType FlatPerSeat()
 * @method static DiscountType Percentage()
 */
final class DiscountType extends PaddleEnum
{
    private const Flat = 'flat';
    private const FlatPerSeat = 'flat_per_seat';
    private const Percentage = 'percentage';
}
