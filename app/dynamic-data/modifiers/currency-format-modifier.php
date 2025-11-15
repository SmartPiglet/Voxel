<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Currency_Format_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Currency format', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'currency_format';
	}

	public function expects(): array {
		return [ static::TYPE_NUMBER ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'select',
			'label' => _x( 'Currency', 'modifiers', 'voxel-backend' ),
			'choices' => [ 'default' => 'Default platform currency' ] + \Voxel\Utils\Currency_List::all(),
		] );

		$this->define_arg( [
			'type' => 'select',
			'label' => _x( 'Amount is in cents', 'modifiers', 'voxel-backend' ),
			'choices' => [ '' => 'No', '1' => 'Yes' ],
		] );
	}

	public function apply( string $value ) {
		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		$amount_is_in_cents = !! $this->get_arg(1);

		$currency = $this->get_arg(0);
		if ( empty( $currency ) || $currency === 'default' ) {
			$currency = \Voxel\get_primary_currency();
		}

		// 'default_mods' backward compatibility (< v1.5)
		if ( $this->get_arg(1) === 'true' ) {
			$currency = \Voxel\get_primary_currency();
			$amount_is_in_cents = false;
		}

		return \Voxel\currency_format( $value, $currency, $amount_is_in_cents );
	}

}
