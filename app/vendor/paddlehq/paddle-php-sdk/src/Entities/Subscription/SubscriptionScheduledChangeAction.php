<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\Subscription;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static SubscriptionScheduledChangeAction Cancel()
 * @method static SubscriptionScheduledChangeAction Pause()
 * @method static SubscriptionScheduledChangeAction Resume()
 */
final class SubscriptionScheduledChangeAction extends PaddleEnum
{
    private const Cancel = 'cancel';
    private const Pause = 'pause';
    private const Resume = 'resume';
}
