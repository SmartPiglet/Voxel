<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities\Notification;

use Voxel\Vendor\Paddle\SDK\Entities\Entity;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CurrencyCodePayouts;
class NotificationPayout implements Entity
{
    private function __construct(public string $id, public NotificationPayoutStatus $status, public string $amount, public CurrencyCodePayouts $currencyCode)
    {
    }
    public static function from(array $data): self
    {
        return new self($data['id'], NotificationPayoutStatus::from($data['status']), $data['amount'], CurrencyCodePayouts::from($data['currency_code']));
    }
}
