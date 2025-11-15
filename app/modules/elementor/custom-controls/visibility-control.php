<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Visibility_Control extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'voxel-visibility';
	}

	protected function get_default_settings() {
		return [
			'label_block' => true,
		];
	}

	public function content_template() {
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="vx-visibility-rules elementor-control-input-wrapper"></div>
			<div class="vx-visibility-edit elementor-control-input-wrapper">
				<a href="#" class="elementor-button elementor-button-default vx-rules-edit"><?= __( 'Edit rules', 'voxel-backend' ) ?></a>
				<a href="#" class="elementor-button elementor-button-default vx-rules-remove"><?= __( 'Remove', 'voxel-backend' ) ?></a>
			</div>
		</div>
		<?php
	}

}
