<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nested_Repeater_Control extends \Voxel\Modules\Elementor\Custom_Controls\Repeater_Control {

	const CONTROL_TYPE = 'nested-elements-repeater';

	public function get_type() {
		return static::CONTROL_TYPE;
	}

	public function get_value( $control, $settings ) {
		$rows = [];
		$value = \Elementor\Base_Data_Control::get_value( $control, $settings );
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;

		if ( ! empty( $value ) ) {
			foreach ( $value as $index => $item ) {
				if ( ! $this->_voxel_should_render( $item ) && empty( $item['_voxel_loop'] ) ) {
					continue;
				}

				if ( ! empty( $item['_voxel_loop'] ) && ! \Voxel\is_importing_elementor_template()  ) {
					\Voxel\Dynamic_Data\Looper::run( $item['_voxel_loop'], [
						'limit' => $item['_voxel_loop_limit'] ?? null,
						'offset' => $item['_voxel_loop_offset'] ?? null,
						'callback' => function( $loop_index ) use ( $control, $item, $controls_manager, &$rows, $index ) {
							foreach ( $control['fields'] as $field ) {
								$control_obj = $controls_manager->get_control( $field['type'] );
								if ( $control_obj instanceof \Elementor\Base_Data_Control ) {
									$item[ $field['name'] ] = $control_obj->get_value( $field, $item );
								}
							}

							if ( ! $this->_voxel_should_render( $item ) ) {
								return;
							}

							$item['_child_index'] = $index;
							$item['_loop_index'] = $loop_index;
							$rows[] = $item;
						},
					] );
				} else {
					foreach ( $control['fields'] as $field ) {
						$control_obj = $controls_manager->get_control( $field['type'] );
						if ( $control_obj instanceof \Elementor\Base_Data_Control ) {
							$item[ $field['name'] ] = $control_obj->get_value( $field, $item );
						}
					}

					$item['_child_index'] = $index;
					$rows[] = $item;
				}
			}
		}

		return $rows;
	}

}
