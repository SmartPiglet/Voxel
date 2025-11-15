<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Relation_Field extends Base_Post_Field {
	use Post_Relation_Field\Models;
	use Post_Relation_Field\Sanitize;
	use Post_Relation_Field\Validate;
	use Post_Relation_Field\Update;
	use Post_Relation_Field\Exports;

	protected $cached_value;

	protected $props = [
		'type' => 'post-relation',
		'label' => 'Post relation',
		'placeholder' => '',
		'relation_type' => 'has_one',
		'post_types' => [],
		'use_custom_key' => false,
		'custom_key' => 'post-relation',
		'allowed_authors' => 'current_author', // current_author|any
		'require_author_approval' => 'never', // never|always
		'allowed_statuses' => [], // publish is always allowed, other statuses can be enabled manually
		'max_count' => null, // maximum relation count for hasMany/belongsToMany relations
		'default' => null,
	];

	public function get_relation_key() {
		return $this->props['use_custom_key'] ? $this->props['custom_key'] : $this->props['key'];
	}

	public function get_value_from_post() {
		if ( $this->cached_value === null ) {
			$select_key = in_array( $this->get_prop('relation_type'), [ 'has_one', 'has_many' ], true ) ? 'child_id' : 'parent_id';
			$cache_key = sprintf( 'relations:%s:%d:%s', $this->get_relation_key(), $this->post->get_id(), $select_key );
			$cache_result = wp_cache_get( $cache_key, 'voxel' );

			if ( is_array( $cache_result ) ) {
				$this->cached_value = $cache_result;
			} else {
				$this->cached_value = $this->get_related_posts();
			}
		}

		return $this->cached_value;
	}

	protected function get_related_posts() {
		global $wpdb;

		$allowed_statuses = array_merge( [ 'publish' ], (array) $this->props['allowed_statuses'] );
		$allowed_statuses = array_map( 'esc_sql', array_filter( $allowed_statuses ) );
		$post_status__in = "'".join( "','", $allowed_statuses )."'";

		if ( in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true ) ) {
			$rows = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT child_id
				FROM {$wpdb->prefix}voxel_relations
				LEFT JOIN {$wpdb->posts} AS p ON child_id = p.ID
				WHERE parent_id = %d
					AND relation_key = %s
					AND p.post_status IN ({$post_status__in})
				ORDER BY `order` ASC
			SQL, $this->post->get_id(), $this->get_relation_key() ) );
		} else {
			$rows = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT parent_id
				FROM {$wpdb->prefix}voxel_relations
				LEFT JOIN {$wpdb->posts} AS p ON parent_id = p.ID
				WHERE child_id = %d
					AND relation_key = %s
					AND p.post_status IN ({$post_status__in})
				ORDER BY `order` ASC
			SQL, $this->post->get_id(), $this->get_relation_key() ) );
		}

		$ids = array_map( 'absint', (array) $rows );

		$is_multiple = in_array( $this->props['relation_type'], [ 'has_many', 'belongs_to_many' ], true );
		if ( ! $is_multiple && ! empty( $ids ) ) {
			$ids = [ array_shift( $ids ) ];
		}

		return $ids;
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_ids = $this->_get_default_ids();
			return ! empty( $default_ids ) ? $default_ids : null;
		} else {
			$approved_ids = $this->get_value();
			$pending_ids = $this->get_pending_ids();
			$ids = array_merge( (array) $approved_ids, (array) $pending_ids );
			if ( empty( $ids ) ) {
				return null;
			}

			return $ids;
		}
	}

	protected function _get_default_ids(): array {
		$ids = [];
		$default_values = $this->render_default_value( $this->get_prop('default') );
		$is_multiple = in_array( $this->props['relation_type'], [ 'has_many', 'belongs_to_many' ], true );
		if ( is_string( $default_values ) && ! empty( $default_values ) ) {
			foreach ( explode( ',', $default_values ) as $default_post_id ) {
				$default_post_id = \Voxel\mb_trim( $default_post_id );
				if ( is_numeric( $default_post_id ) ) {
					$ids[] = absint( $default_post_id );

					if ( ! $is_multiple ) {
						break;
					}
				}
			}
		}

		return $ids;
	}

	protected function frontend_props() {
		if ( $this->is_new_post() ) {
			$ids = $this->_get_default_ids();
		} else {
			$ids = $this->get_value();
		}

		$selected = [];

		if ( ! empty( $ids ) ) {
			$posts = \Voxel\Post::query( [
				'post_type' => ! empty( $this->props['post_types'] ) ? $this->props['post_types'] : 'any',
				'post__in' => $ids,
				'post_status' => array_merge( [ 'publish' ], $this->props['allowed_statuses'] ),
				'posts_per_page' => -1,
			] );

			foreach ( $posts as $post ) {
				$selected[ $post->get_id() ] = [
					'id' => $post->get_id(),
					'title' => $post->get_display_name(),
					'logo' => $post->get_avatar_markup(),
					'type' => $post->post_type->get_singular_name(),
					'icon' => \Voxel\get_icon_markup( $post->post_type->get_icon() ),
				];
			}
		}

		$pending_ids = $this->get_pending_ids();
		if ( ! empty( $pending_ids ) ) {
			$posts = \Voxel\Post::query( [
				'post_type' => ! empty( $this->props['post_types'] ) ? $this->props['post_types'] : 'any',
				'post__in' => $pending_ids,
				'post_status' => array_merge( [ 'publish' ], $this->props['allowed_statuses'] ),
				'posts_per_page' => -1,
			] );

			foreach ( $posts as $post ) {
				$selected[ $post->get_id() ] = [
					'id' => $post->get_id(),
					'title' => $post->get_display_name(),
					'logo' => $post->get_avatar_markup(),
					'type' => $post->post_type->get_singular_name(),
					'icon' => \Voxel\get_icon_markup( $post->post_type->get_icon() ),
					'pending_approval' => true,
				];
			}
		}

		return [
			'multiple' => in_array( $this->props['relation_type'], [ 'has_many', 'belongs_to_many' ], true ),
			'max_count' => is_numeric( $this->props['max_count'] ) ? absint( $this->props['max_count'] ) : null,
			'relation_type' => $this->props['relation_type'],
			'post_types' => $this->props['post_types'],
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'selected' => $selected,
			'require_author_approval' => $this->props['allowed_authors'] === 'any' && $this->props['require_author_approval'] === 'always',
			'pending_ids' => $pending_ids,
		];
	}

	public function sanitize_in_editor( $props ) {
		if ( ! is_array( $props['post_types'] ) ) {
			$props['post_types'] = [];
		}

		$props['post_types'] = array_values( array_filter( $props['post_types'], 'post_type_exists' ) );

		return $props;
	}
}
