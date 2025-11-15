<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Repository {

	public $config;

	private
		$post_type,
		$fields,
		$filters,
		$search_orders;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
		$post_types = \Voxel\get( 'post_types', [] );
		$this->config = $post_types[ $this->post_type->get_key() ] ?? [];
	}

	/**
	 * Memoized method to retrieve, validate field data,
	 * and convert them to their respective classes.
	 *
	 * @since 1.0
	 */
	public function get_fields() {
		if ( is_array( $this->fields ) ) {
			return $this->fields;
		}

		$this->fields = [];

		$config = $this->config['fields'] ?? [];
		$field_types = \Voxel\config('post_types.field_types');

		if ( ! isset( $config[0] ) || ! isset( $config[0]['type'] ) || $config[0]['type'] !== 'ui-step' ) {
			array_unshift( $config, \Voxel\Post_Types\Fields\Ui_Step_Field::preset( [
				'key' => 'step-general',
				'label' => 'General',
			] ) );
		}

		$current_step = $config[0]['key'];
		foreach ( $config as $field_data ) {
			if ( ! is_array( $field_data ) || empty( $field_data['type'] ) || empty( $field_data['key'] ) ) {
				continue;
			}

			if ( isset( $field_types[ $field_data['type'] ] ) ) {
				$field = new $field_types[ $field_data['type'] ]( $field_data );
				$field->set_post_type( $this->post_type );

				$this->fields[ $field->get_key() ] = $field;

				if ( $field->get_type() === 'ui-step' ) {
					$current_step = $field->get_key();
				} else {
					$field->set_step( $current_step );
				}
			}
		}

		// if there are no fields added for this post type yet, use title field automatically
		if ( count( $this->fields ) <= 1 && ! isset( $this->fields['title'] ) ) {
			$title_field = new $field_types['title'];
			$title_field->set_post_type( $this->post_type );
			$title_field->set_step( $current_step );
			$this->fields['title'] = $title_field;
		}

		if ( $this->post_type->get_key() === 'collection' ) {
			$this->_collection_fields();
		}

		return $this->fields;
	}

	private function _collection_fields() {
		if ( ! isset( $this->fields['items'] ) || $this->fields['items']->get_type() !== 'post-relation' ) {
			$field = new \Voxel\Post_Types\Fields\Post_Relation_Field( [
				'label' => 'Items',
				'key' => 'items',
				'relation_type' => 'has_many',
			] );
			$field->set_post_type( $this->post_type );
			$field->set_step( 'step-general' );
			$this->fields['items'] = $field;
		}

		$this->fields['items']->set_prop( 'relation_type', 'has_many' );
		$this->fields['items']->set_prop( 'use_custom_key', false );
		$this->fields['items']->set_prop( 'allowed_authors', 'any' );
		$this->fields['items']->set_prop( 'require_author_approval', 'never' );
	}

	public function get_field( $field_key ) {
		if ( $field_key === 'voxel:promotion' ) {
			return $this->_get_promotion_field();
		} else {
			$fields = $this->get_fields();
			return $fields[ $field_key ] ?? null;
		}
	}

	protected $_get_promotion_field = null;
	protected function _get_promotion_field() {
		if ( $this->_get_promotion_field === null ) {
			$this->_get_promotion_field = new \Voxel\Post_Types\Fields\Product_Field( [
				'label' => 'Promote post',
				'key' => 'voxel:promotion',
				'product-types' => [ 'voxel:promotion' ],
			] );
		}

		return $this->_get_promotion_field;
	}

	/**
	 * Memoized method to retrieve, validate filter data,
	 * and convert them to their respective classes.
	 *
	 * @since 1.0
	 */
	public function get_filters() {
		if ( is_array( $this->filters ) ) {
			return $this->filters;
		}

		$this->filters = [];

		$search = $this->config['search'] ?? [];
		$config = $search['filters'] ?? [];
		$filter_types = \Voxel\config('post_types.filter_types');

		foreach ( $config as $filter_data ) {
			if ( ! is_array( $filter_data ) || empty( $filter_data['type'] ) || empty( $filter_data['key'] ) ) {
				continue;
			}

			if ( isset( $filter_types[ $filter_data['type'] ] ) ) {
				$filter = new $filter_types[ $filter_data['type'] ]( $filter_data );
				$filter->set_post_type( $this->post_type );

				$this->filters[ $filter->get_key() ] = $filter;
			}
		}

		return $this->filters;
	}

	public function get_filter( $filter_key ) {
		$filters = $this->get_filters();
		return $filters[ $filter_key ] ?? null;
	}

	public function get_orderby_filter() {
		foreach ( $this->get_filters() as $filter ) {
			if ( $filter->get_type() === 'order-by' ) {
				return $filter;
			}
		}
	}

	/**
	 * Memoized method to retrieve, validate search order data,
	 * and convert them to their respective classes.
	 *
	 * @since 1.0
	 */
	public function get_search_orders() {
		if ( is_array( $this->search_orders ) ) {
			return $this->search_orders;
		}

		$this->search_orders = [];

		$search = $this->config['search'] ?? [];
		$config = array_values( $search['order'] ?? [] );

		foreach ( $config as $i => $order_data ) {
			if ( ! is_array( $order_data ) || empty( $order_data['key'] ) || empty( $order_data['clauses'] ) ) {
				continue;
			}

			$group = new \Voxel\Post_Types\Order_By\Order_By_Group( $order_data, $this->post_type );
			$this->search_orders[ $order_data['key'] ] = $group;
		}

		return $this->search_orders;
	}

	public function get_search_order( $search_order_key ) {
		$search_orders = $this->get_search_orders();
		return $search_orders[ $search_order_key ] ?? null;
	}

	/**
	 * Save post type configuration to database.
	 *
	 * @since 1.0
	 */
	public function set_config( $new_config ) {
		$post_types = \Voxel\get( 'post_types', [] );

		if ( isset( $new_config['settings'] ) ) {
			$this->config['settings'] = $new_config['settings'];
		}

		if ( isset( $new_config['fields'] ) ) {
			$this->config['fields'] = $new_config['fields'];
		}

		if ( isset( $new_config['search'] ) ) {
			$this->config['search'] = $new_config['search'];
		}

		if ( isset( $new_config['templates'] ) ) {
			$this->config['templates'] = $new_config['templates'];
		}

		if ( isset( $new_config['custom_templates'] ) ) {
			$this->config['custom_templates'] = $new_config['custom_templates'];
		}

		$post_types[ $this->post_type->get_key() ] = $this->config;

        // cleanup post_types array
        foreach ( $post_types as $post_type_key => $post_type_settings ) {
        	if ( ! is_string( $post_type_key ) || empty( $post_type_key ) || empty( $post_type_settings ) ) {
        		unset( $post_types[ $post_type_key ] );
        	}
        }

		\Voxel\set( 'post_types', $post_types );
	}

	public function get_config() {
		return $this->config;
	}

	public function remove() {
		$post_types = \Voxel\get( 'post_types', [] );
		unset( $post_types[ $this->post_type->get_key() ] );
		\Voxel\set( 'post_types', $post_types );
	}

	public function get_editor_config() {
		$settings = $this->get_settings();
		$search = $this->get_search();

		return [
			'settings' => [
				'key' => $this->post_type->get_key(),
				'singular' => $settings['singular'] ?? $this->post_type->get_singular_name(),
				'plural' => $settings['plural'] ?? $this->post_type->get_plural_name(),
				'icon' => $settings['icon'] ?? '',
				'timeline' => [
					'enabled' => $settings['timeline']['enabled'] ?? false,
					'wall' => $settings['timeline']['wall'] ?? 'disabled', // public|followers_only|customers_only|disabled
					'reviews' => $settings['timeline']['reviews'] ?? 'disabled', // public|followers_only|customers_only|disabled
					'visibility' => $settings['timeline']['visibility'] ?? 'public', // public|logged_in|followers_only|customers_only|private
					'wall_visibility' => $settings['timeline']['wall_visibility'] ?? 'public', // public|logged_in|followers_only|customers_only|private
					'review_visibility' => $settings['timeline']['review_visibility'] ?? 'public', // public|logged_in|followers_only|customers_only|private
				],
				'reviews' => [
					'categories' => array_values( $this->post_type->reviews->get_categories() ),
					'rating_levels' => [
						'excellent' => [
							'label' => $settings['reviews']['rating_levels']['excellent']['label'] ?? '',
							'color' => $settings['reviews']['rating_levels']['excellent']['color'] ?? '',
						],
						'very_good' => [
							'label' => $settings['reviews']['rating_levels']['very_good']['label'] ?? '',
							'color' => $settings['reviews']['rating_levels']['very_good']['color'] ?? '',
						],
						'good' => [
							'label' => $settings['reviews']['rating_levels']['good']['label'] ?? '',
							'color' => $settings['reviews']['rating_levels']['good']['color'] ?? '',
						],
						'fair' => [
							'label' => $settings['reviews']['rating_levels']['fair']['label'] ?? '',
							'color' => $settings['reviews']['rating_levels']['fair']['color'] ?? '',
						],
						'poor' => [
							'label' => $settings['reviews']['rating_levels']['poor']['label'] ?? '',
							'color' => $settings['reviews']['rating_levels']['poor']['color'] ?? '',
						],
					],
					'input_mode' => $settings['reviews']['input_mode'] ?? 'numeric', // numeric|stars
					'icons' => [
						'active' => $settings['reviews']['icons']['active'] ?? '',
						'inactive' => $settings['reviews']['icons']['inactive'] ?? '',
					],
				],
				'messages' => $this->post_type->config('settings.messages'),
				'submissions' => [
					'enabled' => $settings['submissions']['enabled'] ?? true,
					'status' => $settings['submissions']['status'] ?? 'publish', // publish|pending
					'update_status' => $settings['submissions']['update_status'] ?? 'publish', // publish|pending|pending_merge|disabled
					'update_slug' => $settings['submissions']['update_slug'] ?? true,
					'deletable' => $settings['submissions']['deletable'] ?? false,
				],
				'quick_search' => $this->post_type->config('settings.quick_search'),
				'map' => $this->post_type->config('settings.map'),
				'permalinks' => $this->post_type->config('settings.permalinks'),
				'indexing' => [
					'post_statuses' => $settings['indexing']['post_statuses'] ?? [],
				],
				'options' => $this->post_type->config('settings.options'),
				'expiration' => [
					'rules' => $settings['expiration']['rules'] ?? [],
				],
			],
			'fields' => array_map( function( $field ) {
				return $field->get_props();
			}, array_values( $this->post_type->get_fields() ) ),
			'search' => [
				'filters' => array_map( function( $filter ) {
					return $filter->get_props();
				}, array_values( $this->post_type->get_filters() ) ),
				'order' => array_map( function( $search_order ) {
					return $search_order->get_props();
				}, array_values( $this->post_type->get_search_orders() ) ),
			],
			'templates' => $this->post_type->templates->get_templates(),
			'custom_templates' => $this->post_type->templates->get_custom_templates(),
		];
	}

	public function get_settings() {
		return $this->config['settings'] ?? [];
	}

	public function get_setting( $setting_key, $default = null ) {
		$settings = $this->get_settings();
		$keys = explode( '.', $setting_key );
		foreach ( $keys as $key ) {
			if ( ! isset( $settings[ $key ] ) ) {
				return $default;
			}

			$settings = $settings[ $key ];
		}

		return $settings;
	}

	public function get_search() {
		return $this->config['search'] ?? [];
	}

	public function is_managed_by_voxel() {
		return ! empty( $this->config );
	}

	/* Static methods */

	public static function get_all() {
		return array_filter( array_map(
			'\Voxel\Post_Type::get',
			get_post_types( [], 'objects' )
		) );
	}

	public static function get_voxel_types() {
		return array_filter( static::get_all(), function( $type ) {
			return $type->is_managed_by_voxel();
		} );
	}

	public static function get_other_types() {
		return array_filter( static::get_all(), function( $type ) {
			return ! $type->is_managed_by_voxel();
		} );
	}

	public function get_expiration_rules() {
		$rules = $this->config['settings']['expiration']['rules'] ?? [];
		if ( ! is_array( $rules ) ) {
			return [];
		}

		$valid_rules = [];

		foreach ( $rules as $rule ) {
			if ( empty( $rule['type'] ) ) {
				continue;
			}

			if ( $rule['type'] === 'fixed' ) {
				if ( ( $rule['amount'] ?? 0 ) >= 1 ) {
					$valid_rules[] = [
						'type' => 'fixed',
						'amount' => absint( $rule['amount'] ),
					];
				}
			} elseif ( $rule['type'] === 'field' ) {
				$field = $this->get_field( $rule['field'] ?? null );
				if ( $field && in_array( $field->get_type(), [ 'recurring-date', 'date' ], true ) ) {
					$valid_rules[] = [
						'type' => 'field',
						'field' => $field->get_key(),
					];
				}
			}
		}

		return $valid_rules;
	}

	public function get_indexable_statuses() {
		$statuses = [
			'publish' => true,
		];

		$custom_statuses = (array) $this->get_setting( 'indexing.post_statuses' );
		foreach ( $custom_statuses as $status ) {
			if ( ! empty( $status ) ) {
				$statuses[ $status ] = true;
			}
		}

		return $statuses;
	}

	public function get_indexable_status_sql() {
		$indexable_statuses = array_keys( $this->get_indexable_statuses() );
		return '\''.join( '\',\'', array_map( 'esc_sql', $indexable_statuses ) ).'\'';
	}
}
