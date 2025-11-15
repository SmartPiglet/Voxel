<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Taxonomy_Field extends Base_Post_Field {
	use Taxonomy_Field\Exports;

	protected $supported_conditions = ['taxonomy'];

	protected $props = [
		'type' => 'taxonomy',
		'label' => 'Taxonomy',
		'taxonomy' => '',
		'placeholder' => '',
		'multiple' => true,
		'display_as' => 'popup',
		'backend_edit_mode' => 'custom_field', // custom_field or native_metabox
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'taxonomy' => [
				'type' => Form_Models\Taxonomy_Select_Model::class,
				'label' => 'Choose taxonomy',
				'classes' => 'x-col-12',
				'post_type' => $this->post_type->get_key(),
			],

			'display_as' => [
				'type' => Form_Models\Select_Model::class,
				'label' => 'Display as',
				'classes' => 'x-col-12',
				'choices' => [
					'popup' => 'Popup',
					'inline' => 'Inline',
				],
			],

			'multiple' => [
				'type' => Form_Models\Switcher_Model::class,
				'label' => 'Allow selection of multiple terms?',
				'classes' => 'x-col-12',
			],

			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model( [
				'placeholder' => 'Enter term slug. Separate multiple values with commas e.g. term_a, term_b, term_c...',
			] ),
			'hidden' => $this->get_hidden_model(),

			'backend_edit_mode' => [
				'type' => Form_Models\Select_Model::class,
				'label' => 'When editing posts through the WordPress backend, edit this field using',
				'classes' => 'x-col-12',
				'v-if' => '!repeater',
				'choices' => [
					'custom_field' => 'Fields Metabox: Display this field alongside other custom fields',
					'native_metabox' => 'Native Metabox: Use the standard WordPress taxonomy metabox',
				],
				'description' => 'To display the native metabox on the block editor, you must also set "Show this taxonomy in REST API" to "Yes" in the taxonomy settings.',
			],
		];
	}

	public function get_available_terms() {
		if ( empty( $this->get_prop('taxonomy') ) ) {
			return [];
		}

		return \Voxel\Term::query( [
			'taxonomy' => $this->get_prop('taxonomy'),
			'hide_empty' => false,
		] );
	}

	public function is_selected( $term ) {
		static $terms;
		if ( is_null( $terms ) ) {
			$terms = array_map( function( $term ) {
				return $term->get_id();
			}, $this->get_value() );
		}

		return in_array( $term->get_id(), $terms, true );
	}

	public function sanitize( $value ) {
		$value = array_map( function( $item ) {
			return (string) $item;
		}, $value );

		// @todo: validate single selection by getting the inner-most term only
		/*if ( ! $this->props['multiple'] ) {
			$value = isset( $value[0] ) ? [ $value[0] ] : [];
		}*/

		return array_filter( (array) $value, function( $slug ) {
			return term_exists( (string) $slug, $this->props['taxonomy'] );
		} );
	}

	public function update( $value ): void {
		if ( ! taxonomy_exists( $this->props['taxonomy'] ) ) {
			return;
		}

		$ids = [];
		$terms = \Voxel\Term::query( [
			'taxonomy' => $this->props['taxonomy'],
			'slug' => ! empty( $value ) ? $value : [''],
			'hide_empty' => false,
		] );

		if ( is_wp_error( $terms ) ) {
			error_log( $terms->get_error_message() );
			return;
		}

		foreach ( $terms as $term ) {
			$ids = array_merge( $ids, [ $term->get_id() ], $term->get_ancestor_ids() );
		}

		wp_set_object_terms(
			$this->post->get_id(),
			array_unique( $ids ),
			$this->props['taxonomy']
		);
	}

	public function get_value_from_post() {
		$terms = get_the_terms( $this->post->get_id(), $this->get_prop('taxonomy') );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return [];
		}

		usort( $terms, function( $a, $b ) {
			return ( (int) $a->voxel_order ) <=> ( (int) $b->voxel_order );
		} );

		$terms = ! is_wp_error( $terms ) ? $terms : [];
		return array_map( '\Voxel\Term::get', $terms );
	}

	public function get_value_from_repeater() {
		$taxonomy = \Voxel\Taxonomy::get( $this->props['taxonomy'] );
		if ( ! $taxonomy ) {
			return null;
		}

		$value = parent::get_value_from_repeater();
		$terms = \Voxel\Term::query( [
			'taxonomy' => $taxonomy->get_key(),
			'hide_empty' => false,
			'orderby' => 'slug__in',
			'slug' => ! empty( $value ) ? $value : [''],
		] );

		return $terms;
	}

	public function check_dependencies() {
		$taxonomy = \Voxel\Taxonomy::get( $this->props['taxonomy'] );
		if ( ! $taxonomy ) {
			throw new \Exception( 'Taxonomy not set.' );
		}
	}

	protected function frontend_props() {
		$taxonomy = \Voxel\Taxonomy::get( $this->props['taxonomy'] );
		$args = [
			'orderby' => 'default',
		];

		$transient_key = sprintf( 'field:%s.%s.%s', $this->post_type->get_key(), $this->get_key(), $taxonomy->get_key() );
		$t = get_transient( $transient_key );

		$terms = ( is_array( $t ) && isset( $t['terms'] ) ) ? $t['terms'] : [];
		$time = ( is_array( $t ) && isset( $t['time'] ) ) ? $t['time'] : 0;
		$hash = ( is_array( $t ) && isset( $t['hash'] ) ) ? $t['hash'] : false;
		$new_hash = md5( wp_json_encode( $args ) );

		if ( ! $t || ( $time < $taxonomy->get_version() ) || $hash !== $new_hash ) {
			$terms = \Voxel\get_terms( $this->props['taxonomy'], $args );
			set_transient( $transient_key, [
				'terms' => $terms,
				'time' => time(),
				'hash' => $new_hash,
			], 14 * DAY_IN_SECONDS );
			// dump('from query');
		} else {
			// dump('from cache');
		}

		$selected = [];
		if ( $this->is_new_post() ) {
			$selected_terms = $this->_get_default_value();
		} else {
			$selected_terms = $this->get_value();
		}

		if ( $selected_terms ) {
			foreach ( $selected_terms as $term ) {
				$selected[ $term->get_slug() ] = [
					'id' => $term->get_id(),
					'label' => $term->get_label(),
					'slug' => $term->get_slug(),
					'icon' => \Voxel\get_icon_markup( $term->get_icon() ),
				];
			}
		}

		return [
			'terms' => $terms,
			'selected' => (object) $selected,
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'multiple' => (bool) $this->props['multiple'],
			'display_as' => $this->props['display_as'],
			'taxonomy' => [
				'label' => $taxonomy ? $taxonomy->get_label() : '',
			],
		];
	}

	protected function _get_default_value(): array {
		$slugs = [];
		$default_values = $this->render_default_value( $this->get_prop('default') );
		if ( is_string( $default_values ) && ! empty( $default_values ) ) {
			foreach ( explode( ',', $default_values ) as $term_slug ) {
				$term_slug = \Voxel\mb_trim( $term_slug );
				if ( ! empty( $term_slug ) ) {
					$slugs[] = $term_slug;
				}
			}
		}

		if ( empty( $slugs ) ) {
			return [];
		}

		$terms = \Voxel\Term::query( [
			'taxonomy' => $this->get_prop('taxonomy'),
			'slug' => $slugs,
			'hide_empty' => false,
		] );

		return $terms;
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$terms = array_map( function( $term ) {
				return $term->get_slug();
			}, $this->_get_default_value() );

			return ! empty( $terms ) ? $terms : null;
		} else {
			$terms = array_map( function( $term ) {
				return $term->get_slug();
			}, (array) $this->get_value() );

			return ! empty( $terms ) ? $terms : null;
		}
	}

	public function export_to_personal_data() {
		$terms = $this->get_value();
		if ( empty( $terms ) ) {
			return null;
		}

		return join( ', ', array_map( function( $term ) {
			return $term->get_label();
		}, $terms ) );
	}
}
