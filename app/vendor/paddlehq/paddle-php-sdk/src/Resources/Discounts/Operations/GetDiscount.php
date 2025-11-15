<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Discounts\Operations;

use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\InvalidArgumentException;
use Voxel\Vendor\Paddle\SDK\HasParameters;
class GetDiscount implements HasParameters
{
    /**
     * @param array<DiscountInclude> $includes
     *
     * @throws InvalidArgumentException On invalid array contents
     */
    public function __construct(private readonly array $includes = [])
    {
        if ($invalid = array_filter($this->includes, fn($value): bool => !$value instanceof DiscountInclude)) {
            throw InvalidArgumentException::arrayContainsInvalidTypes('includes', DiscountInclude::class, implode(', ', $invalid));
        }
    }
    public function getParameters(): array
    {
        return array_filter(['include' => implode(',', $this->includes)]);
    }
}
