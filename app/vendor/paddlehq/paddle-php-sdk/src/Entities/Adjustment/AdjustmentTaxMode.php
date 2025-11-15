<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities\Adjustment;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static AdjustmentTaxMode External()
 * @method static AdjustmentTaxMode Internal()
 */
final class AdjustmentTaxMode extends PaddleEnum
{
    private const External = 'external';
    private const Internal = 'internal';
}
