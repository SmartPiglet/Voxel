<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Update;

use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPrice;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPriceWithProduct;
class TransactionUpdateItemWithPrice
{
    public function __construct(public TransactionNonCatalogPrice|TransactionNonCatalogPriceWithProduct $price, public int $quantity)
    {
    }
}
