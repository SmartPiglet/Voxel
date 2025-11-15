<?php

namespace Voxel\Utils\Object_Fields;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Base_Model_Helpers {

	protected function get_label_model() {
		return [
			'type' => Form_Models\Text_Model::class,
			'label' => 'Field Name',
			'classes' => 'x-col-4',
		];
	}

	protected function get_placeholder_model() {
		return [
			'type' => Form_Models\Text_Model::class,
			'label' => 'Placeholder',
			'classes' => 'x-col-4',
		];
	}

	protected function get_key_model() {
		return [
			'type' => Form_Models\Key_Model::class,
			'label' => 'Field Key',
			'description' => 'Enter a unique field key',
			'classes' => 'x-col-4',
			'ref' => 'keyInput',
		];
	}

	protected function get_required_model() {
		return [
			'type' => Form_Models\Switcher_Model::class,
			'label' => 'Is required?',
			'classes' => 'x-col-12',
		];
	}

	protected function get_hidden_model() {
		return [
			'type' => Form_Models\Switcher_Model::class,
			'label' => 'Is hidden?',
			'classes' => 'x-col-12',
			'infobox' => 'If checked, this field will be visually hidden in the submission form. Field value can be set using the "Prefill value" option.',
		];
	}

	protected function get_minlength_model() {
		return [
			'type' => Form_Models\Number_Model::class,
			'label' => 'Minlength',
			'classes' => 'x-col-3',
			'min' => 0,
		];
	}

	protected function get_maxlength_model() {
		return [
			'type' => Form_Models\Number_Model::class,
			'label' => 'Maxlength',
			'classes' => 'x-col-3',
			'min' => 0,
		];
	}

	protected function get_editor_type_model() {
		return [
			'type' => Form_Models\Select_Model::class,
			'label' => 'Editor type',
			'width' => '1/1',
			'choices' => [
				'plain-text' => 'Plain text',
				'wp-editor-basic' => 'WP Editor &mdash; Basic controls',
				'wp-editor-advanced' => 'WP Editor &mdash; Advanced controls',
			],
		];
	}

	protected function get_icon_model() {
		return [
			'type' => Form_Models\Icon_Model::class,
			'label' => 'Icon',
			'classes' => 'x-col-12',
		];
	}

	protected function get_description_model() {
		return [
			'type' => Form_Models\Textarea_Model::class,
			'label' => 'Description',
			'classes' => 'x-col-12',
		];
	}

	protected function get_model( $model_key, $overrides = [] ) {
		$method_name = sprintf( 'get_%s_model', $model_key );
		if ( method_exists( $this, $method_name ) ) {
			$model = $this->{$method_name}();
			return array_merge( $model, $overrides );
		}
	}

	protected function get_css_class_model() {
		return [
			'type' => Form_Models\Text_Model::class,
			'label' => 'CSS Classes',
			'classes' => 'x-col-12',
		];
	}

	protected function get_default_value_model( array $args = [] ) {
		return [
			'type' => Form_Models\Dtag_Model::class,
			'classes' => is_string( $args['classes'] ?? null ) ? $args['classes'] : 'x-col-12',
			'v-model' => is_string( $args['v-model'] ?? null ) ? $args['v-model'] : null,
			'label' => is_string( $args['label'] ?? null ) ? $args['label'] : 'Prefill value',
			':tag-groups' => '$root.defaultValueDataGroups()',
			'infobox' => is_string( $args['infobox'] ?? null ) ? $args['infobox'] : 'The default value assigned to this field when creating a new post',
			'placeholder' => is_string( $args['placeholder'] ?? null ) ? $args['placeholder'] : 'Enter default value',
		];
	}

	public function render_default_value( $prop ) {
		if ( ! is_string( $prop ) ) {
			return null;
		}

		$wp_page = \get_queried_object();
		if ( $wp_page instanceof \WP_Post ) {
			$page = \Voxel\Post::get( $wp_page );
		} else {
			$page = null;
		}

		return \Voxel\render( $prop, [
			'author' => \Voxel\Dynamic_Data\Group::User( \Voxel\get_current_user() ),
			'site' => \Voxel\Dynamic_Data\Group::Site(),
			'page' => \Voxel\Dynamic_Data\Group::Simple_Post( $page ),
		] );
	}
}
