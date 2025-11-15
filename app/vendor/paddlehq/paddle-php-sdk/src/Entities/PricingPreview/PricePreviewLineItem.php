<?php

declare (strict_types=1);
/**
 * |------
 * | ! Generated code !
 * | Altering this code will result in changes being overwritten |
 * |-------------------------------------------------------------|.
 */
namespace Voxel\Vendor\Paddle\SDK\Entities\PricingPreview;

use Voxel\Vendor\Paddle\SDK\Entities\Price;
use Voxel\Vendor\Paddle\SDK\Entities\Product;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\Totals;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\UnitTotals;
class PricePreviewLineItem
{
    /**
     * @param PricePreviewDiscounts[] $discounts
     */
    private function __construct(public Price $price, public int $quantity, public string $taxRate, public UnitTotals $unitTotals, public PricePreviewUnitTotalsFormatted $formattedUnitTotals, public Totals $totals, public PricePreviewTotalsFormatted $formattedTotals, public Product $product, public array $discounts)
    {
    }
    public static function from(array $data): self
    {
        return new self(Price::from($data['price']), $data['quantity'], $data['tax_rate'], UnitTotals::from($data['unit_totals']), PricePreviewUnitTotalsFormatted::from($data['formatted_unit_totals']), Totals::from($data['totals']), PricePreviewTotalsFormatted::from($data['formatted_totals']), Product::from($data['product']), array_map(fn($item): PricePreviewDiscounts => PricePreviewDiscounts::from($item), $data['discounts']));
    }
}
