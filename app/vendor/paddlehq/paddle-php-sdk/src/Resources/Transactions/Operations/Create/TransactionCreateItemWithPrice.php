<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Create;

use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPrice;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPriceWithProduct;
class TransactionCreateItemWithPrice
{
    public function __construct(public TransactionNonCatalogPrice|TransactionNonCatalogPriceWithProduct $price, public int $quantity)
    {
    }
}
