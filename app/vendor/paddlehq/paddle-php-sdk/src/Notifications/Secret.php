<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications;

final class Secret
{
    public function __construct(
        #[\SensitiveParameter]
        public readonly string $key
    )
    {
    }
}
