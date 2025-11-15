<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Reports;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\ReportCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Report;
use Voxel\Vendor\Paddle\SDK\Entities\ReportCSV;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Reports\Operations\CreateReport;
use Voxel\Vendor\Paddle\SDK\Resources\Reports\Operations\ListReports;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class ReportsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListReports $listOperation = new ListReports()): ReportCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/reports', $listOperation));
        return ReportCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), ReportCollection::class));
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id): Report
    {
        $parser = new ResponseParser($this->client->getRaw("/reports/{$id}"));
        return Report::from($parser->getData());
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function getReportCsv(string $id): ReportCSV
    {
        $parser = new ResponseParser($this->client->getRaw("/reports/{$id}/download-url"));
        return ReportCSV::from($parser->getData());
    }
    /**
     * @throws ApiError                 On a generic API error
     * @throws ApiError\AddressApiError On an address specific API error
     * @throws MalformedResponse        If the API response was not parsable
     */
    public function create(CreateReport $createOperation): Report
    {
        $parser = new ResponseParser($this->client->postRaw('/reports', $createOperation));
        return Report::from($parser->getData());
    }
}
