<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Adjustments;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Adjustment;
use Voxel\Vendor\Paddle\SDK\Entities\AdjustmentCreditNote;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\AdjustmentCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Adjustments\Operations\CreateAdjustment;
use Voxel\Vendor\Paddle\SDK\Resources\Adjustments\Operations\GetAdjustmentCreditNote;
use Voxel\Vendor\Paddle\SDK\Resources\Adjustments\Operations\ListAdjustments;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class AdjustmentsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListAdjustments $listOperation = new ListAdjustments()): AdjustmentCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/adjustments', $listOperation));
        return AdjustmentCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), AdjustmentCollection::class));
    }
    /**
     * @throws ApiError                    On a generic API error
     * @throws ApiError\AdjustmentApiError On an adjustment specific API error
     * @throws MalformedResponse           If the API response was not parsable
     */
    public function create(CreateAdjustment $createOperation): Adjustment
    {
        $parser = new ResponseParser($this->client->postRaw('/adjustments', $createOperation));
        return Adjustment::from($parser->getData());
    }
    /**
     * @throws ApiError                    On a generic API error
     * @throws ApiError\AdjustmentApiError On an adjustment specific API error
     * @throws MalformedResponse           If the API response was not parsable
     */
    public function getCreditNote(string $id, GetAdjustmentCreditNote $getOperation = new GetAdjustmentCreditNote()): AdjustmentCreditNote
    {
        $parser = new ResponseParser($this->client->getRaw("/adjustments/{$id}/credit-note", $getOperation));
        return AdjustmentCreditNote::from($parser->getData());
    }
}
