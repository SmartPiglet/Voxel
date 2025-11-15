<?php

namespace Voxel\Post_Types\Filters\Traits;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Model_Helpers {

	protected function get_label_model() {
		return [
			'type' => Form_Models\Text_Model::class,
			'label' => 'Label',
			'classes' => 'x-col-6',
		];
	}

	protected function get_description_model() {
		return [
			'type' => Form_Models\Textarea_Model::class,
			'label' => 'Description',
			'classes' => 'x-col-12',
		];
	}

	protected function get_placeholder_model() {
		return [
			'type' => Form_Models\Text_Model::class,
			'label' => 'Placeholder',
			'classes' => 'x-col-6',
		];
	}

	protected function get_key_model() {
		return [
			'type' => Form_Models\Key_Model::class,
			'label' => 'Form Key',
			'description' => 'Enter a unique form key',
			'classes' => 'x-col-6',
			'classes' => 'field-key-wrapper',
			'format' => 'slug',
		];
	}

	protected function get_icon_model() {
		return [
			'type' => Form_Models\Icon_Model::class,
			'label' => 'Icon',
			'classes' => 'x-col-12',
		];
	}

	protected function get_source_model( $field_types ) {
		return function() use ( $field_types ) { ?>
			<div class="ts-form-group x-col-6">
				<label>Data source:</label>
				<select v-model="filter.source">
					<option v-for="field in $root.getFieldsByType( <?= esc_attr( wp_json_encode( (array) $field_types ) ) ?> )" :value="field.key">
						{{ field.label }}
					</option>
				</select>
			</div>
		<?php };
	}

	protected function get_model( $model_key, $overrides = [] ) {
		$method_name = sprintf( 'get_%s_model', $model_key );
		if ( method_exists( $this, $method_name ) ) {
			$model = $this->{$method_name}();
			return array_merge( $model, $overrides );
		}
	}
}
