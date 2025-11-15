<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gallery_Control extends \Elementor\Control_Gallery {

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );

		// get image ids from dynamic tags
		if ( strncmp( $value[0]['id'] ?? '', '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$ids = explode( ',', \Voxel\render( $value[0]['id'] ) );
			$value = array_map( function( $id ) {
				return [
					'id' => $id,
					'url' => '',
				];
			}, $ids );
		}

		return $value;
	}

	public function on_import( $settings ) {
		foreach ( $settings as &$attachment ) {
			if ( empty( $attachment['url'] ) || str_starts_with( $attachment['id'], '@tags()' ) ) {
				continue;
			}

			$attachment = \Elementor\Plugin::$instance->templates_manager->get_import_images_instance()->import( $attachment );
		}

		$settings = array_filter( $settings );

		return $settings;
	}
}
