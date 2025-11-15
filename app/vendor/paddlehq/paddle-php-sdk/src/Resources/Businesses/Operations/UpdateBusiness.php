<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Businesses\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\Contacts;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Status;
use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Undefined;
class UpdateBusiness implements \JsonSerializable
{
    use FiltersUndefined;
    /**
     * @param array<Contacts> $contacts
     */
    public function __construct(public readonly string|Undefined $name = new Undefined(), public readonly string|Undefined|null $companyNumber = new Undefined(), public readonly string|Undefined|null $taxIdentifier = new Undefined(), public readonly array|Undefined $contacts = new Undefined(), public readonly CustomData|Undefined|null $customData = new Undefined(), public readonly Status|Undefined $status = new Undefined())
    {
    }
    public function jsonSerialize(): array
    {
        return $this->filterUndefined(['name' => $this->name, 'company_number' => $this->companyNumber, 'tax_identifier' => $this->taxIdentifier, 'contacts' => $this->contacts, 'custom_data' => $this->customData, 'status' => $this->status]);
    }
}
