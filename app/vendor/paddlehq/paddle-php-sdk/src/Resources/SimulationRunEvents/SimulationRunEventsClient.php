<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\SimulationRunEvents;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\SimulationRunEventCollection;
use Voxel\Vendor\Paddle\SDK\Entities\SimulationRunEvent;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\SimulationRunEvents\Operations\ListSimulationRunEvents;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class SimulationRunEventsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(string $simulationId, string $runId, ListSimulationRunEvents $listOperation = new ListSimulationRunEvents()): SimulationRunEventCollection
    {
        $parser = new ResponseParser($this->client->getRaw("/simulations/{$simulationId}/runs/{$runId}/events", $listOperation));
        return SimulationRunEventCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), SimulationRunEventCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $simulationId, string $runId, string $id): SimulationRunEvent
    {
        $parser = new ResponseParser($this->client->getRaw("/simulations/{$simulationId}/runs/{$runId}/events/{$id}"));
        return SimulationRunEvent::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function replay(string $simulationId, string $runId, string $id): SimulationRunEvent
    {
        $parser = new ResponseParser($this->client->postRaw("/simulations/{$simulationId}/runs/{$runId}/events/{$id}/replay"));
        return SimulationRunEvent::from($parser->getData());
    }
}
