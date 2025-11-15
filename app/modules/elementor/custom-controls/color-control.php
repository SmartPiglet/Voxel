<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Color_Control extends \Elementor\Control_Color {

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );
		if ( is_string( $value ) && strncmp( $value, '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$value = \Voxel\render( $value );
		}

		return $value;
	}
}
