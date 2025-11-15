<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK;

class Options
{
    public function __construct(public Environment $environment = Environment::PRODUCTION, public int $retries = 1)
    {
    }
}
