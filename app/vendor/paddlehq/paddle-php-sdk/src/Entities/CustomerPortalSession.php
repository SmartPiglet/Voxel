<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities;

use Voxel\Vendor\Paddle\SDK\Entities\CustomerPortalSession\CustomerPortalSessionUrls;
use Voxel\Vendor\Paddle\SDK\Notifications\Entities\Entity;
class CustomerPortalSession implements Entity
{
    private function __construct(public string $id, public string $customerId, public CustomerPortalSessionUrls $urls, public \DateTimeInterface $createdAt)
    {
    }
    public static function from(array $data): self
    {
        return new self(id: $data['id'], customerId: $data['customer_id'], urls: CustomerPortalSessionUrls::from($data['urls']), createdAt: DateTime::from($data['created_at']));
    }
}
