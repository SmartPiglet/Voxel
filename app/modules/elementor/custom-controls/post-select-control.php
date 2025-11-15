<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Select_Control extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'voxel-post-select';
	}

	protected function get_default_settings() {
		return [
			'label' => '',
			'label_block' => true,
			'post_type' => [],
		];
	}

	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>

		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo $control_uid; ?>" class="elementor-control-title">
					{{{ data.label }}}
				</label>
			<# } #>
			<div class="elementor-control-input-wrapper">
				<div class="value-wrap" style="display: none;">
					<a href="<?= esc_url( admin_url('post.php?post=:id&action=edit') ) ?>" target="_blank" class="current-value"></a>
					<a href="#" class="clear-value"><?= __( 'Clear', 'voxel-backend' ) ?></a>
				</div>
				<input type="text" placeholder="<?= esc_attr( __( 'Search templates', 'voxel-backend' ) ) ?>">
				<div class="search-results"></div>
			</div>
		</div>

		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

	public function get_value( $control, $settings ) {
		$value = parent::get_value( $control, $settings );
		if ( is_string( $value ) && strncmp( $value, '@tags()', 7 ) === 0 && ! \Voxel\is_importing_elementor_template() ) {
			$value = \Voxel\render( $value );
		}

		// cache ids to bulk retrieve post titles for display in the editor
		if ( is_admin() && ! empty( $value ) ) {
			if ( ! isset( $GLOBALS['_vx_post_select_values'] ) ) {
				$GLOBALS['_vx_post_select_values'] = [];
			}

			$GLOBALS['_vx_post_select_values'][] = $value;
		}

		return $value;
	}
}
