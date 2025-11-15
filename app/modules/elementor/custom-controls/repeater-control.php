<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repeater_Control extends \Elementor\Control_Repeater {

	public function get_value( $control, $settings ) {
		$rows = [];
		$value = \Elementor\Base_Data_Control::get_value( $control, $settings );
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;

		if ( ! empty( $value ) ) {
			foreach ( $value as $item ) {
				if ( ! $this->_voxel_should_render( $item ) && empty( $item['_voxel_loop'] ) ) {
					continue;
				}

				if ( ! empty( $item['_voxel_loop'] ) && is_string( $item['_voxel_loop'] ) && ! \Voxel\is_importing_elementor_template()  ) {
					\Voxel\Dynamic_Data\Looper::run( $item['_voxel_loop'], [
						'limit' => $item['_voxel_loop_limit'] ?? null,
						'offset' => $item['_voxel_loop_offset'] ?? null,
						'callback' => function() use ( $control, $item, $controls_manager, &$rows ) {
							foreach ( $control['fields'] as $field ) {
								$control_obj = $controls_manager->get_control( $field['type'] );
								if ( $control_obj instanceof \Elementor\Base_Data_Control ) {
									$item[ $field['name'] ] = $control_obj->get_value( $field, $item );
								}
							}

							if ( ! $this->_voxel_should_render( $item ) ) {
								return;
							}

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

					$rows[] = $item;
				}
			}
		}

		return $rows;
	}

	public function content_template() {
		?>
		<label>
			<span class="elementor-control-title">{{{ data.label }}}</span>
		</label>
		<div class="elementor-repeater-fields-wrapper {{{ data._disable_loop ? 'voxel-loop-disabled' : '' }}} {{{ data._disable_visibility_rules ? 'voxel-visibility-disabled' : '' }}}"></div>
		<# if ( itemActions.add ) { #>
			<div class="elementor-button-wrapper">
				<button class="elementor-button elementor-repeater-add" type="button">
					<i class="eicon-plus" aria-hidden="true"></i><?= __( 'Add Item', 'voxel-backend' ); ?>
				</button>
			</div>
		<# } #>
		<?php
	}

	protected function _voxel_should_render( $item ) {
		if ( \Voxel\is_edit_mode() || \Voxel\is_elementor_ajax() || \Voxel\is_rendering_css() ) {
			return true;
		}

		$behavior = $item['_voxel_visibility_behavior'] ?? null;
		$rules = $item['_voxel_visibility_rules'] ?? null;

		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return true;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );
		if ( $behavior === 'hide' ) {
			return $rules_passed ? false : true;
		} else {
			return $rules_passed ? true : false;
		}
	}
}
