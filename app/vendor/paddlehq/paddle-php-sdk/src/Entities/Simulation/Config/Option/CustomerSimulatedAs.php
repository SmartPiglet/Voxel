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
 * @method static CustomerSimulatedAs New()
 * @method static CustomerSimulatedAs ExistingEmailMatched()
 * @method static CustomerSimulatedAs ExistingDetailsPrefilled()
 */
final class CustomerSimulatedAs extends PaddleEnum
{
    private const New = 'new';
    private const ExistingEmailMatched = 'existing_email_matched';
    private const ExistingDetailsPrefilled = 'existing_details_prefilled';
}
