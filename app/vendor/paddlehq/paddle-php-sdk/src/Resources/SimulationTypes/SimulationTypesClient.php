<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\SimulationTypes;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\SimulationTypeCollection;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class SimulationTypesClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(): SimulationTypeCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/simulation-types'));
        return SimulationTypeCollection::from($parser->getData());
    }
}
