<?php

namespace Voxel\Dynamic_Data\Data_Groups\Post;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Simple_Post_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'simple-post';
	}

	protected static $instances = [];
	public static function get( \Voxel\Post $post ): self {
		if ( ! array_key_exists( $post->get_id(), static::$instances ) ) {
			static::$instances[ $post->get_id() ] = new static( $post );
		}

		return static::$instances[ $post->get_id() ];
	}

	public static function unset( int $post_id ) {
		unset( static::$instances[ $post_id ] );
	}

	public $post;
	protected function __construct( \Voxel\Post $post ) {
		$this->post = $post;
	}

	protected function properties(): array {
		return [
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
			'visit_stats' => $this->get_visit_stats(),
			':logo' => Tag::Number('Logo ID')->render( function() {
				return $this->post->get_logo_id();
			} )->hidden(),
		];
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
			':stats' => 'visit_stats',
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Post::mock() );
	}

	protected function get_visit_stats(): Base_Data_Type {
		return Tag::Object('Visit stats')->properties( function() {
			return [
				'views' => Tag::Object('Views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return $this->post->stats->get_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return $this->post->stats->get_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return $this->post->stats->get_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return $this->post->stats->get_views('all');
						} ),
					];
				} ),
				'unique_views' => Tag::Object('Unique views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return $this->post->stats->get_unique_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return $this->post->stats->get_unique_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return $this->post->stats->get_unique_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return $this->post->stats->get_unique_views('all');
						} ),
					];
				} ),
				'countries' => Tag::Object_List('Top countries')->items( function() {
					return $this->post->stats->get_tracking_stats('countries');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Country name')->render( function() use ( $item ) {
							if ( ! is_array( $item ) ) {
								return '';
							}

							$list = \Voxel\Utils\Country_List::all();
							return $list[ $item['item'] ?? '' ]['name'] ?? '';
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
						'code' => Tag::String('Country code')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
					];
				} ),
				'ref_domains' => Tag::Object_List('Top referrers (domains)')->items( function() {
					return $this->post->stats->get_tracking_stats('ref_domains');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Domain name')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
						'count' => Tag::Number('Referral count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'ref_urls' => Tag::Object_List('Top referrers (URLs)')->items( function() {
					return $this->post->stats->get_tracking_stats('ref_urls');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('URL')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
						'count' => Tag::Number('Referral count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'browsers' => Tag::Object_List('Top browsers')->items( function() {
					return $this->post->stats->get_tracking_stats('browsers');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Browser')->render( function() use ( $item ) {
							return \Voxel\Stats\get_browser_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'platforms' => Tag::Object_List('Top platforms')->items( function() {
					return $this->post->stats->get_tracking_stats('platforms');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Platform')->render( function() use ( $item ) {
							return \Voxel\Stats\get_platform_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'devices' => Tag::Object_List('Devices')->items( function() {
					return $this->post->stats->get_tracking_stats('devices');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Device')->render( function() use ( $item ) {
							return \Voxel\Stats\get_device_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'last_updated' => Tag::Date('Last updated')->render( function() {
					$time = $this->post->stats->get_last_updated_time();
					if ( $time === null ) {
						return '';
					}

					return date( 'Y-m-d H:i:s', $time );
				} ),
			];
		} );
	}
}
