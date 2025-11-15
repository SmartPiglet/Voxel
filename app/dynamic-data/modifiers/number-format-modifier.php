<?php

namespace Voxel\Dynamic_Data\Modifiers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Format_Modifier extends Base_Modifier {

	public function get_label(): string {
		return _x( 'Number format', 'modifiers', 'voxel-backend' );
	}

	public function get_key(): string {
		return 'number_format';
	}

	public function expects(): array {
		return [ static::TYPE_NUMBER ];
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Decimals', 'modifiers', 'voxel-backend' ),
			'description' => _x( 'Specify the number of decimal places to round to. Negative values round to positions before the decimal point. Default: 0.', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function apply( string $value ) {
		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		$decimals = $this->get_arg(0);
		if ( ! is_numeric( $decimals ) ) {
			$decimals = 0;
		}

		if ( $decimals < 0 ) {
			$exp = absint( $decimals );
			$rounded = round( $value / pow( 10, $exp ) ) * pow( 10, $exp );

			$formatted = number_format_i18n( $rounded, 0 );
			if ( ! is_null( $formatted ) ) {
				$value = $formatted;
			}

			return $value;
		} else {
			$formatted = number_format_i18n( $value, $decimals );
			if ( ! is_null( $formatted ) ) {
				$value = $formatted;
			}

			return $value;
		}
	}

}
