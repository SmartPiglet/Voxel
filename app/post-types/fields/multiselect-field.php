<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Multiselect_Field extends Base_Post_Field {

	protected $supported_conditions = ['taxonomy'];

	protected $props = [
		'type' => 'multiselect',
		'label' => 'Multi-select',
		'placeholder' => '',
		'choices' => [],
		'display_as' => 'popup',
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'display_as' => [
				'type' => Form_Models\Select_Model::class,
				'label' => 'Display as',
				'classes' => 'x-col-12',
				'choices' => [
					'popup' => 'Popup',
					'inline' => 'Inline',
				],
			],
			'choices' => function() { ?>
				<div v-if="field.__view === 'list'" class="ts-form-group x-col-12">
					<label>Choices <a href="#" style="float: right;" @click.prevent="field.__view = null">Plain view</a></label>
					<select-field-choices :field="field"></select-field-choices>
				</div>
				<div v-else class="ts-form-group x-col-12">
					<label>Choices <a href="#" style="float: right;" @click.prevent="field.__view = 'list'">List view</a></label>
					<choices-input :field="field"></choices-input>
				</div>
			<?php },
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model( [
				'placeholder' => 'Enter default value(s) e.g. Choice A|Choice B...',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		if ( ! is_array( $value ) ) {
			return null;
		}

		$choices = $this->get_choices();
		$selected = [];

		foreach ( $value as $choice ) {
			if ( isset( $choices[ $choice ] ) ) {
				$selected[] = $choice;
			}
		}

		if ( empty( $selected ) ) {
			return null;
		}

		return $selected;
	}

	public function update( $value ): void {
		if ( ! is_array( $value ) || empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $value ) ) );
		}
	}

	public function get_value_from_post() {
		return (array) json_decode( get_post_meta( $this->post->get_id(), $this->get_key(), true ), true );
	}

	public function get_choices(): array {
		$choices = [];
		$order = 0;
		foreach ( $this->props['choices'] as $choice ) {
			if ( ! ( is_string( $choice['value'] ?? null ) && is_string( $choice['label'] ?? null ) ) ) {
				continue;
			}

			if ( ! empty( $choice['icon'] ) ) {
				$choice['icon'] = \Voxel\get_icon_markup( $choice['icon'] );
			}

			$choice['order'] = $order;
			$choices[ $choice['value'] ] = $choice;

			$order++;
		}

		return $choices;
	}

	protected function _get_default_value(): array {
		$value = [];
		$default_values = $this->render_default_value( $this->get_prop('default') );
		if ( is_string( $default_values ) && ! empty( $default_values ) ) {
			foreach ( explode( '|', $default_values ) as $choice ) {
				$choice = \Voxel\mb_trim( $choice );
				if ( ! empty( $choice ) ) {
					$value[] = $choice;
				}
			}
		}

		return $value;
	}

	public function get_selected_choices(): array {
		if ( $this->is_new_post() ) {
			$value = $this->_get_default_value();
		} else {
			$value = $this->get_value();
		}

		if ( ! is_array( $value ) || empty( $value ) ) {
			return [];
		}

		$choices = $this->get_choices();
		$selected = [];

		foreach ( $value as $choice ) {
			if ( isset( $choices[ $choice ] ) ) {
				$selected[ $choice ] = $choices[ $choice ];
			}
		}

		uasort( $selected, function( $a, $b ) {
			return $a['order'] <=> $b['order'];
		} );

		return $selected;
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_value = $this->_get_default_value();
			return ! empty( $default_value ) ? $default_value : null;
		} else {
			return $this->get_value();
		}
	}

	protected function frontend_props() {
		return [
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'choices' => array_values( $this->get_choices() ),
			'selected' => (object) $this->get_selected_choices(),
			'display_as' => $this->props['display_as'],
		];
	}

	public function dynamic_data() {
		return Tag::Object_List( $this->get_label() )->items( function() {
			return $this->get_selected_choices();
		} )->properties( function( $index, $item ) {
			return [
				'value' => Tag::String('Value')->render( function() use ( $item ) {
					return $item['value'];
				} ),
				'label' => Tag::String('Label')->render( function() use ( $item ) {
					return $item['label'];
				} ),
				'icon' => Tag::String('Icon')->render( function() use ( $item ) {
					return $item['icon'] ?? '';
				} ),
			];
		} );
	}

	public function export_to_personal_data() {
		return join( ', ', array_map( function( $choice ) {
			return $choice['label'];
		}, $this->get_selected_choices() ) );
	}
}
