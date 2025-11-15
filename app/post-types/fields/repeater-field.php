<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Repeater_Field extends Base_Post_Field {

	protected $props = [
		'type' => 'repeater',
		'label' => 'Repeater',
		'min' => null,
		'max' => null,
		'fields' => [],
		'row_label' => null,
		'l10n_item' => 'Item',
		'l10n_add_row' => 'Add row',
	];

	protected $validation_bypass_required = false;

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),

			'row_label' => function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Row label</label>
					<select v-model="field.row_label">
						<option value=""></option>
						<template v-for="field in field.fields">
							<option v-if="['text','number','phone','email','date','select','multiselect','url','taxonomy','color','post-relation','time'].includes(field.type)" :value="field.key">{{ field.label }}</option>
						</template>
					</select>
				</div>
			<?php },

			'l10n_item' => [
				'type' => Form_Models\Text_Model::class,
				'label' => 'Default row label',
				'classes' => 'x-col-3',
			],

			'l10n_add_row' => [
				'type' => Form_Models\Text_Model::class,
				'label' => 'Add row label',
				'classes' => 'x-col-3',
			],

			'min' => [
				'type' => Form_Models\Number_Model::class,
				'label' => 'Minimum repeater items',
				'classes' => 'x-col-3',
			],
			'max' => [
				'type' => Form_Models\Number_Model::class,
				'label' => 'Maximum repeater items',
				'classes' => 'x-col-3',
			],
			'css_class' => $this->get_css_class_model(),
		];
	}

	public function sanitize( $rows ) {
		if ( ! is_array( $rows ) ) {
			return [];
		}

		$sanitized = [];
		foreach ( (array) $rows as $row_index => $row ) {
			foreach ( $this->get_fields() as $field ) {
				if ( ! $field->passes_visibility_rules() ) {
					$sanitized[ $row_index ][ $field->get_key() ] = null;
				} else {
					$field->set_repeater_index( $row_index );
					if ( ! isset( $row[ $field->get_key() ] ) ) {
						$sanitized[ $row_index ][ $field->get_key() ] = null;
					} else {
						$sanitized[ $row_index ][ $field->get_key() ] = $field->sanitize( $row[ $field->get_key() ] );
					}
				}
			}
		}

		return $sanitized;
	}

	public function check_validity_bypass_required( $value ) {
		$this->validation_bypass_required = true;
		parent::check_validity_bypass_required( $value );
	}

	public function validate( $rows ): void {
		$min_rows = is_numeric( $this->get_model_value('min') ) ? absint( $this->get_model_value('min') ) : null;
		if ( $min_rows && count( $rows ) < $min_rows ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( '@field_name must contain at least @min entries', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@min' => $min_rows,
				] )
			);
		}

		$max_rows = is_numeric( $this->get_model_value('max') ) ? absint( $this->get_model_value('max') ) : null;
		if ( $max_rows && count( $rows ) > $max_rows ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( '@field_name cannot contain more than @max entries', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@max' => $max_rows,
				] )
			);
		}

		foreach ( $rows as $row_index => $row ) {
			foreach ( $this->get_fields() as $field ) {
				if ( ! ( $field->passes_visibility_rules() && $field->passes_conditional_logic( $row ) ) ) {
					continue;
				}

				$field->set_repeater_index( $row_index );

				try {
					if ( $this->validation_bypass_required ) {
						$field->check_validity_bypass_required( $row[ $field->get_key() ] );
					} else {
						$field->check_validity( $row[ $field->get_key() ] );
					}
				} catch ( \Exception $e ) {
					throw $e;
				}
			}
		}
	}

	public function update( $rows ): void {
		$rows = $this->_prepare_rows_for_storage( $rows );

		if ( empty( $rows ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $rows ) ) );
		}
	}

	public function update_value_in_repeater( $rows ) {
		return $this->_prepare_rows_for_storage( $rows );
	}

	protected function _prepare_rows_for_storage( $rows ) {
		$previous_values = $this->get_value();

		foreach ( $rows as $row_index => $row ) {
			foreach ( $this->get_fields() as $field ) {
				if ( ! ( $field->passes_visibility_rules() && $field->passes_conditional_logic( $row ) ) ) {
					// prevent field value from getting unset when the repeater is edited by a user that
					// does not meet the visibility rules for that field
					if ( isset( $previous_values[ $row_index ][ $field->get_key() ] ) ) {
						$rows[ $row_index ][ $field->get_key() ] = $previous_values[ $row_index ][ $field->get_key() ];
					} else {
						unset( $rows[ $row_index ][ $field->get_key() ] );
					}
				} else {
					$field->set_post( $this->post );
					$field->set_repeater_index( $row_index );

					if ( $row[ $field->get_key() ] === null ) {
						unset( $rows[ $row_index ][ $field->get_key() ] );
						continue;
					}

					$value = $field->update_value_in_repeater( $row[ $field->get_key() ] );
					if ( $value === null ) {
						unset( $rows[ $row_index ][ $field->get_key() ] );
						continue;
					}

					$rows[ $row_index ][ $field->get_key() ] = $value;
				}
			}

			if ( empty( $row ) ) {
				unset( $rows[ $row_index ] );
			}
		}

		return $rows;
	}

	public function get_value_from_post() {
		return (array) json_decode( get_post_meta(
			$this->post->get_id(), $this->get_key(), true
		), ARRAY_A );
	}

	public function get_fields() {
		$fields = [];

		$config = $this->props['fields'] ?? [];
		$field_types = \Voxel\config('post_types.field_types');

		foreach ( $config as $field_data ) {
			if ( ! is_array( $field_data ) || empty( $field_data['type'] ) || empty( $field_data['key'] ) ) {
				continue;
			}

			if ( isset( $field_types[ $field_data['type'] ] ) ) {
				$field = new $field_types[ $field_data['type'] ]( $field_data );
				$field->set_post_type( $this->post_type );
				$field->set_repeater( $this );
				$field->set_step( $this->get_step() );

				if ( $this->post ) {
					$field->set_post( $this->post );
				}

				try {
					$field->check_dependencies();
				} catch ( \Exception $e ) {
					continue;
				}

				$fields[ $field->get_key() ] = $field;
			}
		}

		return $fields;
	}

	public function get_required_scripts(): array {
		$scripts = [
			'sortable' => true,
			'vue-draggable' => true,
		];

		foreach ( $this->get_fields() as $field ) {
			foreach ( $field->get_required_scripts() as $script_handle ) {
				$scripts[ $script_handle ] = true;
			}
		}

		return array_keys( $scripts );
	}

	protected function frontend_props(): array {
		$value = $this->get_value();
		$fields = $this->get_fields();

		$rows = [];
		foreach ( (array) $value as $repeater_index => $row ) {
			foreach ( $fields as $_field ) {
				if ( ! $_field->passes_visibility_rules() ) {
					continue;
				}

				$field = clone $_field;
				$field->set_repeater_index( $repeater_index );
				$rows[ $repeater_index ][ $field->get_key() ] = $field->get_frontend_config();
			}

			$rows[ $repeater_index ]['meta:state'] = [
				'key' => 'meta:state',
				'collapsed' => true,
			];
		}

		$config = [];
		foreach ( $fields as $_field ) {
			if ( ! $_field->passes_visibility_rules() ) {
				continue;
			}

			$field = clone $_field;
			$field->set_repeater_index(-1); // to be used as blueprint for new rows, value must be null
			$config[ $field->get_key() ] = $field->get_frontend_config();
		}

		$config['meta:state'] = [
			'key' => 'meta:state',
			'collapsed' => false,
			'label' => '',
		];

		return [
			'fields' => $config,
			'rows' => $rows,
			'row_label' => $this->props['row_label'],
			'l10n' => [
				'item' => $this->props['l10n_item'],
				'add_row' => $this->props['l10n_add_row'],
			],
			'min_rows' => is_numeric( $this->get_model_value('min') ) ? absint( $this->get_model_value('min') ) : null,
			'max_rows' => is_numeric( $this->get_model_value('max') ) ? absint( $this->get_model_value('max') ) : null,
		];
	}

	protected function editing_value() {
		return null;
	}

	public function get_field_templates() {
		$templates = [];
		foreach ( $this->get_fields() as $field ) {
			if ( $template = locate_template( sprintf( 'templates/widgets/create-post/%s-field.php', $field->get_type() ) ) ) {
				$templates[] = $template;
			}

			if ( $field->get_type() === 'repeater' ) {
				$templates = array_merge( $templates, $field->get_field_templates() );
			}
		}

		return $templates;
	}

	protected function get_row( $index ) {
		$rows = $this->get_value();
		if ( ! ( is_array( $rows ) && isset( $rows[ $index ] ) ) ) {
			return null;
		}

		return $rows[ $index ];
	}

	public function dynamic_data() {
		$fields = $this->get_fields();
		return Tag::Object_List( $this->get_label() )->items( function() {
			return array_keys( (array) $this->get_value() );
		} )->properties( function( $index ) use ( $fields ) {
			$properties = [];
			foreach ( $fields as $field ) {
				$field = clone $field;
				$field->set_repeater_index( $index );
				$exports = $field->dynamic_data();
				if ( $exports !== null ) {
					$properties[ $field->get_key() ] = $exports;
				}
			}

			return $properties;
		} );
	}

	public function export_to_personal_data() {
		$fields = $this->get_fields();
		$values = [];

		foreach ( (array) $this->get_value() as $repeater_index => $row ) {
			$values[ $repeater_index ] = [];
			foreach ( $fields as $_field ) {
				$field = clone $_field;
				$field->set_repeater_index( $repeater_index );

				$rules = $field->get_prop('visibility_rules');
				if ( is_array( $rules ) && ! empty( $rules ) ) {
					continue;
				}

				$export_value = $field->get_value_for_personal_data_exporter();
				if ( empty( $export_value ) ) {
					continue;
				}

				$values[ $repeater_index ][] = sprintf( '<b>%s</b><br>%s', $field->get_label(), $export_value );
			}

			$values[ $repeater_index ] = join( '<br><br>', $values[ $repeater_index ] );
		}

		$values = array_filter( $values );
		if ( empty( $values ) ) {
			return null;
		}

		$details = [];
		foreach ( $values as $i => $value ) {
			$rownum = $i+1;
			$details[] = <<<HTML
				<details>
					<summary><b>{$this->get_label()} #{$rownum}</b></summary>
					{$value}
				</details>
			HTML;
		}

		return join( '<hr>', $details );
	}

	protected function overridable_models(): array {
		return [
			'min' => [
				'type' => 'number',
				'label' => 'Minimum repeater items',
			],
			'max' => [
				'type' => 'number',
				'label' => 'Maximum repeater items',
			],
		];
	}
}
