<?php

namespace Voxel\Product_Types\Cart;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Guest_Cart extends Base_Cart {

	public function get_type(): string {
		return 'guest_cart';
	}

	public function update(): void {
		//
	}

	public function get_payment_method(): ?string {
		return null;
	}
}
