<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Adjustments\Operations;

use Voxel\Vendor\Paddle\SDK\Entities\Shared\Disposition;
use Voxel\Vendor\Paddle\SDK\HasParameters;
class GetAdjustmentCreditNote implements HasParameters
{
    public function __construct(private readonly Disposition|null $disposition = null)
    {
    }
    public function getParameters(): array
    {
        return array_filter(['disposition' => $this->disposition?->getValue()]);
    }
}
