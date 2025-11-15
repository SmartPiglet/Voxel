<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Products\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\CatalogType;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Status;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxCategory;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class UpdateProduct implements \JsonSerializable
{
    use FiltersUndefined;
    public function __construct(public readonly string|Undefined $name = new Undefined(), public readonly string|Undefined|null $description = new Undefined(), public readonly CatalogType|Undefined|null $type = new Undefined(), public readonly TaxCategory|Undefined $taxCategory = new Undefined(), public readonly string|Undefined|null $imageUrl = new Undefined(), public readonly CustomData|Undefined|null $customData = new Undefined(), public readonly Status|Undefined $status = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['name' => $this->name, 'description' => $this->description, 'type' => $this->type, 'tax_category' => $this->taxCategory, 'image_url' => $this->imageUrl, 'custom_data' => $this->customData, 'status' => $this->status]);
    }
}
