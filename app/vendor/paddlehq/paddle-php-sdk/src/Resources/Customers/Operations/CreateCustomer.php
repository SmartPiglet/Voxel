<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Customers\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class CreateCustomer implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly string $email, public readonly string|Undefined|null $name = new Undefined(), public readonly CustomData|Undefined|null $customData = new Undefined(), public readonly string|Undefined $locale = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['email' => $this->email, 'name' => $this->name, 'custom_data' => $this->customData, 'locale' => $this->locale]);
    }
}
