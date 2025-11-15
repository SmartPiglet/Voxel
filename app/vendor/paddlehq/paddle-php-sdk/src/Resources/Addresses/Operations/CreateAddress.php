<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Addresses\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CountryCode;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class CreateAddress implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly CountryCode $countryCode, public readonly string|Undefined|null $description = new Undefined(), public readonly string|Undefined|null $firstLine = new Undefined(), public readonly string|Undefined|null $secondLine = new Undefined(), public readonly string|Undefined|null $city = new Undefined(), public readonly string|Undefined|null $postalCode = new Undefined(), public readonly string|Undefined|null $region = new Undefined(), public readonly CustomData|Undefined|null $customData = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['country_code' => $this->countryCode, 'description' => $this->description, 'first_line' => $this->firstLine, 'second_line' => $this->secondLine, 'city' => $this->city, 'postal_code' => $this->postalCode, 'region' => $this->region, 'custom_data' => $this->customData]);
    }
}
