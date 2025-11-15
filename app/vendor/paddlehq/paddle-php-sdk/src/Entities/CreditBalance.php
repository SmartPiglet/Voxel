<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities;

use Voxel\Vendor\Paddle\SDK\Entities\Adjustment\AdjustmentCustomerBalance;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CurrencyCode;
class CreditBalance implements Entity
{
    private function __construct(public string $customerId, public CurrencyCode $currencyCode, public AdjustmentCustomerBalance $balance)
    {
    }
    public static function from(array $data): self
    {
        return new self($data['customer_id'], CurrencyCode::from($data['currency_code']), AdjustmentCustomerBalance::from($data['balance']));
    }
}
