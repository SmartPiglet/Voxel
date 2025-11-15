<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Select_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'select',
		'label' => 'Select',
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
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		$value = sanitize_text_field( $value );
		$choice_exists = false;
		foreach ( $this->props['choices'] as $choice ) {
			if ( $choice['value'] === $value ) {
				$choice_exists = true;
				break;
			}
		}

		if ( ! $choice_exists ) {
			return null;
		}

		return $value;
	}

	public function update( $value ): void {
		if ( $this->is_empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( $value ) );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	public function get_selected_choice() {
		$value = $this->get_value();
		foreach ( $this->props['choices'] as $choice ) {
			if ( (string) $choice['value'] === (string) $value ) {
				return $choice;
			}
		}

		return null;
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			return $this->render_default_value( $this->get_prop('default') );
		} else {
			return $this->get_value();
		}
	}

	protected function frontend_props() {
		$choices = $this->props['choices'];
		$prepared_choices = [];
		foreach ( $choices as $choice ) {
			$value = (string) ( $choice['value'] ?? '' );
			$label = (string) ( $choice['label'] ?? '' );
			$icon = (string) ( $choice['icon'] ?? '' );

			if ( mb_strlen( $value ) < 1 ) {
				continue;
			}

			if ( mb_strlen( $label ) < 1 ) {
				$label = $value;
			}

			if ( ! empty( $icon ) ) {
				$icon = \Voxel\get_icon_markup( $icon );
			}

			$prepared_choices[] = [
				'value' => $value,
				'label' => $label,
				'icon' => $icon,
			];
		}

		return [
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'choices' => $prepared_choices,
			'display_as' => $this->props['display_as'],
		];
	}

	public function dynamic_data() {
		return Tag::Object( $this->get_label() )->properties( function() {
			return [
				'value' => Tag::String('Value')->render( function() {
					return $this->get_value();
				} ),
				'label' => Tag::String('Label')->render( function() {
					$choice = $this->get_selected_choice();
					return $choice['label'] ?? null;
				} ),
				'icon' => Tag::String('Icon')->render( function() {
					$choice = $this->get_selected_choice();
					return $choice['icon'] ?? null;
				} ),
			];
		} );
	}

	public function export_to_personal_data() {
		$choice = $this->get_selected_choice();
		return $choice['label'] ?? null;
	}
}
