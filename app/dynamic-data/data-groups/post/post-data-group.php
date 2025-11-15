<?php

namespace Voxel\Dynamic_Data\Data_Groups\Post;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {
	use Review_Data, Timeline_Data, Wall_Data, Visits_Data;

	public function get_type(): string {
		return 'post';
	}

	protected static $instances = [];
	public static function get( \Voxel\Post $post ): self {
		if ( ! $post->post_type ) {
			return null;
		}

		if ( ! array_key_exists( $post->get_id(), static::$instances ) || $post->is_mock_post() ) {
			static::$instances[ $post->get_id() ] = new static( $post );
		}

		return static::$instances[ $post->get_id() ];
	}

	public static function unset( int $post_id ) {
		unset( static::$instances[ $post_id ] );
	}

	public $post;
	public $post_type;
	protected function __construct( \Voxel\Post $post ) {
		$this->post = $post;
		$this->post_type = $post->post_type;
	}

	public function get_post(): \Voxel\Post {
		return $this->post;
	}

	protected function properties(): array {
		$properties = [
			'id' => Tag::Number('ID')->render( function() {
				return $this->post->get_id() ?: '';
			} ),
			'title' => Tag::String('Title')->render( function() {
				$post_type = $this->post->post_type;
				if ( $post_type && $post_type->get_key() === 'profile' ) {
					return $this->post->get_display_name();
				}

				return $this->post->get_title();
			} ),
			'content' => Tag::String('Content')->render( function() {
				// prevent memory leak when elementor uses $document->save_plain_text();
				if ( \Voxel\is_elementor_ajax() ) {
					if ( did_action( 'elementor/db/before_save' ) ) {
						return '';
					}

					return $this->post->get_content();
				}

				return apply_filters( 'the_content', $this->post->get_content() );
			} ),
			'slug' => Tag::String('Slug')->render( function() {
				return $this->post->get_slug();
			} ),
			'permalink' => Tag::URL('Permalink')->render( function() {
				return $this->post->get_link();
			} ),
			'edit_link' => Tag::URL('Edit link')->render( function() {
				return $this->post->get_edit_link();
			} ),
			'post_type' => Tag::Object('Post type')->properties( function() {
				return [
					'key' => Tag::String('Key')->render( function() {
						$post_type = $this->post->post_type;
						return $post_type ? $post_type->get_key() : '';
					} ),
					'singular' => Tag::String('Singular name')->render( function() {
						$post_type = $this->post->post_type;
						return $post_type ? $post_type->get_singular_name() : '';
					} ),
					'plural' => Tag::String('Plural name')->render( function() {
						$post_type = $this->post->post_type;
						return $post_type ? $post_type->get_plural_name() : '';
					} ),
				];
			} ),
			'status' => Tag::Object('Status')->properties( function() {
				return [
					'key' => Tag::String('Key')->render( function() {
						return $this->post->get_status();
					} ),
					'label' => Tag::String('Label')->render( function() {
						global $wp_post_statuses;
						return $wp_post_statuses[ $this->post->get_status() ]->label ?? '';
					} ),
				];
			} ),
			'date_created' => Tag::Date('Date created')->render( function() {
				$wp_post = $this->post->get_wp_post_object();
				return $wp_post->post_date;
			} ),
			'date_modified' => Tag::Date('Last modified date')->render( function() {
				$wp_post = $this->post->get_wp_post_object();
				return $wp_post->post_modified;
			} ),
			'expiry_date' => Tag::Date('Expiration date')->render( function() {
				return $this->post->get_expiry_date() ?? '';
			} ),
			'priority' => Tag::Number('Priority')->render( function() {
				return $this->post->get_priority();
			} ),
			'excerpt' => Tag::String('Excerpt')->render( function() {
				return $this->post->get_excerpt();
			} ),
			'reviews' => $this->get_review_data(),
			'timeline' => $this->get_timeline_data(),
			'wall' => $this->get_wall_data(),
			'followers' => $this->get_followers_data(),
			':logo' => Tag::Number('Logo ID')->render( function() {
				return $this->post->get_logo_id();
			} )->hidden(),
		];

		if ( $this->post->post_type && $this->post->post_type->is_tracking_enabled() ) {
			$properties['visit_stats'] = $this->get_visit_stats();
		}

		if ( $this->post->post_type && $this->post->post_type->get_key() === 'collection' ) {
			$properties[':item_counts'] = $this->get_collection_data();
		}

		foreach ( $this->post->get_fields() as $field ) {
			$exports = $field->dynamic_data();
			if ( $exports === null ) {
				continue;
			}

			$property_key = $field->get_key();
			if ( isset( $properties[ $property_key ] ) ) {
				$property_key = 'field:'.$field->get_key();
			}

			$properties[ $property_key ] = $exports;
		}

		return $properties;
	}

	protected function aliases(): array {
		return [
			':id' => 'id',
			':title' => 'title',
			':content' => 'content',
			':excerpt' => 'excerpt',
			':date' => 'date_created',
			':modified_date' => 'date_modified',
			':expiry_date' => 'expiry_date',
			':url' => 'permalink',
			':edit_url' => 'edit_link',
			':slug' => 'slug',
			':status' => 'status',
			':priority' => 'priority',
			':post_type' => 'post_type',
			':reviews' => 'reviews',
			':timeline' => 'timeline',
			':wall' => 'wall',
			':followers' => 'followers',
			':stats' => 'visit_stats',
		];
	}

	protected function get_followers_data(): Base_Data_Type {
		return Tag::Object('Followers')->properties( function() {
			return [
				'accepted' => Tag::Number('Follow count', 'Number of users that are following this post')->render( function() {
					$stats = $this->post->repository->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_ACCEPTED ] ?? 0 );
				} ),
				/*'requested' => Tag::Number('Follow requested count', 'Number of users that have requested to follow this post')->render( function() {
					$stats = $this->post->repository->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_REQUESTED ] ?? 0 );
				} ),*/
				'blocked' => Tag::Number('Block count', 'Number of users that have been blocked by this post')->render( function() {
					$stats = $this->post->repository->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_BLOCKED ] ?? 0 );
				} ),
			];
		} );
	}

	protected function get_collection_data(): Base_Data_Type {
		return Tag::Object('Item counts')->properties( function() {
			$properties = [];

			$field = $this->post->post_type->get_field('items');
			foreach ( (array) $field->get_prop('post_types') as $post_type_key ) {
				if ( $post_type = \Voxel\Post_Type::get( $post_type_key ) ) {
					$properties[ $post_type->get_key() ] = Tag::Number( $post_type->get_label() )->render( function() use ( $post_type ) {
						$collection_id = $this->post->get_id();

						if ( ! isset( $GLOBALS['vx_collection_counts'] ) ) {
							$GLOBALS['vx_collection_counts'] = [];
						}

						if ( isset( $GLOBALS['vx_collection_counts'][ $collection_id ] ) ) {
							$counts = $GLOBALS['vx_collection_counts'][ $collection_id ];
						} else {
							global $wpdb;
							$counts = $wpdb->get_results( <<<SQL
								SELECT posts.post_type, COUNT(*) AS total FROM {$wpdb->prefix}voxel_relations AS relations
									LEFT JOIN {$wpdb->posts} AS posts ON ( relations.child_id = posts.ID )
								WHERE relations.parent_id = {$collection_id}
									AND relations.relation_key = 'items'
									AND posts.post_status = 'publish'
								GROUP BY post_type
							SQL, OBJECT_K );
							$GLOBALS['vx_collection_counts'][ $collection_id ] = $counts;
						}

						return absint( $counts[ $post_type->get_key() ]->total ?? 0 );
					} );
				}
			}

			return $properties;
		} );
	}

	public function get_export_key(): string {
		return sprintf( 'post_type:%s', $this->post_type->get_key() );
	}

	public function methods(): array {
		return [
			'meta' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\Post_Meta_Method::class,
		];
	}

	public static function mock( string $post_type_key = 'post' ): self {
		return new static( \Voxel\Post::dummy( [ 'post_type' => $post_type_key ] ) );
	}
}
