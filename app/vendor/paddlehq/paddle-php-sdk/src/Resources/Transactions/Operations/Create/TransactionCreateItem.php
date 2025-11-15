<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Create;

class TransactionCreateItem
{
    public function __construct(public string $priceId, public int $quantity)
    {
    }
}
