<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions;

use Voxel\Vendor\Paddle\SDK\Exceptions\SdkException;
class MalformedResponse extends SdkException
{
    public function __construct(public \JsonException $e)
    {
        parent::__construct($e->getMessage(), $e->getCode(), $e->getPrevious());
    }
}
