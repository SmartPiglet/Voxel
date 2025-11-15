<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities\ApiKey;

use Voxel\Vendor\Paddle\SDK\PaddleEnum;
/**
 * @method static ApiKeyStatus Active()
 * @method static ApiKeyStatus Expired()
 * @method static ApiKeyStatus Revoked()
 */
final class ApiKeyStatus extends PaddleEnum
{
    private const Active = 'active';
    private const Expired = 'expired';
    private const Revoked = 'revoked';
}
