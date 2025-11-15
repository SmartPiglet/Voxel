<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Exceptions;

class FieldError
{
    public function __construct(public string $field, public string $error)
    {
    }
}
