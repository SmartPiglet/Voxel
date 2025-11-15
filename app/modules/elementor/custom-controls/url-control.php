<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Url_Control extends \Elementor\Control_URL {

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );
		if ( strncmp( $value['url'], '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$value['url'] = \Voxel\render( $value['url'] );
		}

		return $value;
	}
}
