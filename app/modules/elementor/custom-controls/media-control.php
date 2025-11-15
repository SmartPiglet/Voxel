<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Media_Control extends \Elementor\Control_Media {

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );

		// get image id from dynamic tags
		if ( strncmp( $value['id'], '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$media = \Voxel\render( $value['id'] );

			if ( is_numeric( $media ) ) {
				$value['id'] = $media;
				$value['url'] = wp_get_attachment_image_url( $media, 'full' );
				$value['_dynamic'] = true;
			} else {
				$value['id'] = '';
				$value['url'] = ' ';
			}
		}

		return $value;
	}

	public function on_import( $settings ) {
		if ( empty( $settings['url'] ) || str_starts_with( $settings['id'], '@tags()' ) ) {
			return $settings;
		}

		$settings = \Elementor\Plugin::$instance->templates_manager->get_import_images_instance()->import( $settings );

		if ( ! $settings ) {
			$settings = [
				'id' => '',
				'url' => \Elementor\Utils::get_placeholder_image_src(),
			];
		}

		return $settings;
	}
}
