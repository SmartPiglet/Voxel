<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Revise;

use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class TransactionReviseCustomer implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly string|Undefined $name = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['name' => $this->name]);
    }
}
