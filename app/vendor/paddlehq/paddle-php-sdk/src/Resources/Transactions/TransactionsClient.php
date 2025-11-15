<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Resources\Transactions;

use Voxel\Vendor\Paddle\SDK\Client;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\Paginator;
use Voxel\Vendor\Paddle\SDK\Entities\Collections\TransactionCollection;
use Voxel\Vendor\Paddle\SDK\Entities\Transaction;
use Voxel\Vendor\Paddle\SDK\Entities\TransactionData;
use Voxel\Vendor\Paddle\SDK\Entities\TransactionPreview;
use Voxel\Vendor\Paddle\SDK\Exceptions\ApiError;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\InvalidArgumentException;
use Voxel\Vendor\Paddle\SDK\Exceptions\SdkExceptions\MalformedResponse;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\GetTransactionInvoice;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\List\Includes;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\ListTransactions;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\PreviewTransaction;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\ReviseTransaction;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\UpdateTransaction;
use Voxel\Vendor\Paddle\SDK\ResponseParser;
class TransactionsClient
{
    public function __construct(private readonly Client $client)
    {
    }
    /**
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function list(ListTransactions $listOperation = new ListTransactions()): TransactionCollection
    {
        $parser = new ResponseParser($this->client->getRaw('/transactions', $listOperation));
        return TransactionCollection::from($parser->getData(), new Paginator($this->client, $parser->getPagination(), TransactionCollection::class));
    }
    /**
     * @param Includes[] $includes
     *
     * @throws ApiError          On a generic API error
     * @throws MalformedResponse If the API response was not parsable
     */
    public function get(string $id, array $includes = []): Transaction
    {
        if ($invalid = array_filter($includes, fn($value): bool => !$value instanceof Includes)) {
            throw InvalidArgumentException::arrayContainsInvalidTypes('includes', Includes::class, implode(', ', $invalid));
        }
        $params = $includes === [] ? [] : ['include' => implode(',', array_map(fn($enum) => $enum->getValue(), $includes))];
        $parser = new ResponseParser($this->client->getRaw("/transactions/{$id}", $params));
        return Transaction::from($parser->getData());
    }
    /**
     * @throws ApiError                     On a generic API error
     * @throws ApiError\TransactionApiError On a transaction specific API error
     * @throws MalformedResponse            If the API response was not parsable
     */
    public function create(CreateTransaction $createOperation, array $includes = []): Transaction
    {
        if ($invalid = array_filter($includes, fn($value): bool => !$value instanceof Includes)) {
            throw InvalidArgumentException::arrayContainsInvalidTypes('includes', Includes::class, implode(', ', $invalid));
        }
        $params = $includes === [] ? [] : ['include' => implode(',', array_map(fn($enum) => $enum->getValue(), $includes))];
        $parser = new ResponseParser($this->client->postRaw('/transactions', $createOperation, $params));
        return Transaction::from($parser->getData());
    }
    /**
     * @throws ApiError                     On a generic API error
     * @throws ApiError\TransactionApiError On a transaction specific API error
     * @throws MalformedResponse            If the API response was not parsable
     */
    public function update(string $id, UpdateTransaction $operation): Transaction
    {
        $parser = new ResponseParser($this->client->patchRaw("/transactions/{$id}", $operation));
        return Transaction::from($parser->getData());
    }
    /**
     * @throws ApiError                     On a generic API error
     * @throws ApiError\TransactionApiError On a transaction specific API error
     * @throws MalformedResponse            If the API response was not parsable
     */
    public function preview(PreviewTransaction $operation): TransactionPreview
    {
        $parser = new ResponseParser($this->client->postRaw('/transactions/preview', $operation));
        return TransactionPreview::from($parser->getData());
    }
    /**
     * @throws ApiError                     On a generic API error
     * @throws ApiError\TransactionApiError On a transaction specific API error
     * @throws MalformedResponse            If the API response was not parsable
     */
    public function getInvoicePDF(string $id, GetTransactionInvoice $getOperation = new GetTransactionInvoice()): TransactionData
    {
        $parser = new ResponseParser($this->client->getRaw("/transactions/{$id}/invoice", $getOperation));
        return TransactionData::from($parser->getData());
    }
    /**
     * @throws ApiError                     On a generic API error
     * @throws ApiError\TransactionApiError On a transaction specific API error
     * @throws MalformedResponse            If the API response was not parsable
     */
    public function revise(string $id, ReviseTransaction $operation): Transaction
    {
        $parser = new ResponseParser($this->client->postRaw("/transactions/{$id}/revise", $operation));
        return Transaction::from($parser->getData());
    }
}
