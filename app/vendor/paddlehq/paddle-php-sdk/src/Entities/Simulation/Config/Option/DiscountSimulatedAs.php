<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Option;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static DiscountSimulatedAs NotProvided()
 * @method static DiscountSimulatedAs Prefilled()
 * @method static DiscountSimulatedAs EnteredByCustomer()
 */
final class DiscountSimulatedAs extends PaddleEnum
{
    private const NotProvided = 'not_provided';
    private const Prefilled = 'prefilled';
    private const EnteredByCustomer = 'entered_by_customer';
}
