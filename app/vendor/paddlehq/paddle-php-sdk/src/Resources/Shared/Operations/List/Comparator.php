<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Shared\Operations\List;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static Comparator LT()
 * @method static Comparator LTE()
 * @method static Comparator GT()
 * @method static Comparator GTE()
 */
class Comparator extends PaddleEnum
{
    private const LT = 'LT';
    private const LTE = 'LTE';
    private const GT = 'GT';
    private const GTE = 'GTE';
}
