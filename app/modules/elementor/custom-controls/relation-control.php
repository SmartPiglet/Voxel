<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Relation_Control extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'voxel-relation';
	}

	protected function get_default_settings() {
		return [
			'label_block' => true,
			'vx_group' => '',
			'vx_target' => '',
			'vx_side' => 'left',
			'reload' => 'preview',
		];
	}

	public function content_template() {
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="vx-relation-list elementor-control-input-wrapper"></div>
			<# if ( data.reload === 'editor' ) { #>
				<a href="#" onclick="voxel_reload_editor(); return false;" class="elementor-button">Apply changes</a>
			<# } else { #>
				<a href="#" onclick="voxel_reload_preview(); return false;" class="elementor-button">Apply changes</a>
			<# } #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		</div>
		<?php
	}

}