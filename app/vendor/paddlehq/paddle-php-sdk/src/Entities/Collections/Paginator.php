<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities\Collections;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Pagination;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class Paginator
{
    /**
     * @param class-string<Collection> $mapper
     */
    public function __construct(protected Client $client, protected Pagination $pagination, protected string $mapper)
    {
    }
    public function hasMore(): bool
    {
        return $this->pagination->hasMore;
    }
    /**
     * @throws ApiError On a generic API error
     */
    public function nextPage(): Collection
    {
        $response = $this->client->getRaw($this->pagination->next);
        $responseParser = new ResponseParser($response);
        return $this->mapper::from($responseParser->getData(), new self($this->client, $responseParser->getPagination(), $this->mapper));
    }
}
