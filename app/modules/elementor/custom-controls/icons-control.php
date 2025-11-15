<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Icons_Control extends \Elementor\Control_Icons {

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );

		if ( is_string( $value['value'] ) && strncmp( $value['value'], '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$icon_string = \Voxel\render( $value['value'] );
			$value = \Voxel\parse_icon_string( $icon_string );
		}

		return $value;
	}

}
