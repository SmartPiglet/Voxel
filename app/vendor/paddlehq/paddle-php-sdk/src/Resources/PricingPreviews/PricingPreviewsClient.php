<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\PricingPreviews;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\PricePreview;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\PricingPreviews\Operations\PreviewPrice;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class PricingPreviewsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function previewPrices(PreviewPrice $operation): PricePreview
    {
        $parser = new ResponseParser($this->client->postRaw('/pricing-preview', $operation));
        return PricePreview::from($parser->getData());
    }
}
