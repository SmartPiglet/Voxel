<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation;

use Voxel\Vendor\Paddle\SDK\FiltersUndefined;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\DateTime;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Report\ReportFilter;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Report\ReportStatus;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Report\ReportType;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Simulation\Traits\OptionalProperties;
use Voxel\Vendor\Paddle\SDK\Undefined;
final class Report implements SimulationEntity
{
    use OptionalProperties;
    use FiltersUndefined;
    /**
     * @param array<ReportFilter> $filters
     */
    public function __construct(public readonly string|Undefined $id = new Undefined(), public readonly ReportStatus|Undefined $status = new Undefined(), public readonly int|Undefined|null $rows = new Undefined(), public readonly ReportType|Undefined $type = new Undefined(), public readonly array|Undefined $filters = new Undefined(), public readonly \DateTimeInterface|Undefined|null $expiresAt = new Undefined(), public readonly \DateTimeInterface|Undefined $createdAt = new Undefined(), public readonly \DateTimeInterface|Undefined $updatedAt = new Undefined())
    {
    }
    public static function from(array $data): self
    {
        return new self(id: self::optional($data, 'id'), status: self::optional($data, 'status', fn($value) => ReportStatus::from($value)), rows: self::optional($data, 'rows'), type: self::optional($data, 'type', fn($value) => ReportType::from($value)), filters: self::optionalList($data, 'filters', fn($value) => ReportFilter::from($value)), expiresAt: self::optional($data, 'expires_at', fn($value) => DateTime::from($value)), createdAt: self::optional($data, 'created_at', fn($value) => DateTime::from($value)), updatedAt: self::optional($data, 'updated_at', fn($value) => DateTime::from($value)));
    }
    public function jsonSerialize(): mixed
    {
        return $this->filterUndefined(['id' => $this->id, 'status' => $this->status, 'rows' => $this->rows, 'type' => $this->type, 'filters' => $this->filters, 'expires_at' => $this->expiresAt, 'created_at' => $this->createdAt, 'updated_at' => $this->updatedAt]);
    }
}
