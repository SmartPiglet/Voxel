<?php

namespace Voxel\Dynamic_Data\Data_Groups\Site;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Site_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'site';
	}

	protected static $instance;
	public static function get(): self {
		if ( static::$instance === null ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	protected function properties(): array {
		$properties = [
			'title' => Tag::String('Title')->render( function() {
				return get_bloginfo('name');
			} ),
			'logo' => Tag::Number('Logo')->render( function() {
				return get_theme_mod( 'custom_logo' );
			} ),
			'tagline' => Tag::String('Tagline')->render( function() {
				return get_bloginfo('description');
			} ),
			'url' => Tag::URL('URL')->render( function() {
				return get_bloginfo('url');
			} ),
			'admin_url' => Tag::URL('WP Admin URL')->render( function() {
				return admin_url();
			} ),
			'login_url' => Tag::URL('Login URL')->render( function() {
				return \Voxel\get_auth_url();
			} ),
			'register_url' => Tag::URL('Register URL')->render( function() {
				return add_query_arg( 'register', '', \Voxel\get_auth_url() );
			} ),
			'logout_url' => Tag::URL('Logout URL')->render( function() {
				return \Voxel\get_logout_url();
			} ),
			'current_plan_url' => Tag::URL('Current plan URL')->render( function() {
				return get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/');
			} ),
			'language' => Tag::String('Language')->render( function() {
				return get_bloginfo('language');
			} ),
			'date' => Tag::Date('Date')->render( function() {
				return current_time('Y-m-d H:i:s');
			} ),
			'post_types' => $this->get_post_type_data(),
		];

		if ( ! empty( \Voxel\get('settings.stats.enabled_post_types') ) ) {
			$properties['visit_stats'] = $this->get_visit_stats();
		}

		return $properties;
	}

	protected function aliases(): array {
		return [
			':stats' => 'visit_stats',
		];
	}

	protected function get_post_type_data(): Base_Data_Type {
		return Tag::Object('Post types')->properties( function() {
			$properties = [];

			foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
				$properties[ $post_type->get_key() ] = Tag::Object( $post_type->get_label() )->properties( function() use ( $post_type ) {
					return [
						'singular' => Tag::String('Singular name')->render( function() use ( $post_type ) {
							return $post_type->get_singular_name();
						} ),
						'plural' => Tag::String('Plural name')->render( function() use ( $post_type ) {
							return $post_type->get_plural_name();
						} ),
						'icon' => Tag::String('Icon')->render( function() use ( $post_type ) {
							return $post_type->get_icon();
						} ),
						'archive' => Tag::URL('Archive link')->render( function() use ( $post_type ) {
							return $post_type->get_archive_link();
						} ),
						'create' => Tag::URL('Create post link')->render( function() use ( $post_type ) {
							return $post_type->get_create_post_link();
						} ),
						'templates' => Tag::Object('Templates')->properties( function() use ( $post_type ) {
							return [
								'single' => Tag::Number('Single page')->render( function() use ( $post_type ) {
									return $post_type->get_templates()['single'];
								} ),
								'card' => Tag::Number('Preview card')->render( function() use ( $post_type ) {
									return $post_type->get_templates()['card'];
								} ),
								'archive' => Tag::Number('Archive')->render( function() use ( $post_type ) {
									return $post_type->get_templates()['archive'];
								} ),
								'form' => Tag::Number('Create post')->render( function() use ( $post_type ) {
									return $post_type->get_templates()['form'];
								} ),
								'custom' => Tag::Object('Custom')->properties( function() use ( $post_type ) {
									$properties = [];

									foreach ( $post_type->templates->get_custom_templates() as $group => $items ) {
										foreach ( $items as $item ) {
											$key = sprintf( '%s:%s', $group, $item['label'] );
											$label = sprintf( '%s: %s', $group === 'single' ? 'Single page' : 'Preview card', $item['label'] );

											$properties[ $key ] = Tag::Number( $label )->render( function() use ( $item ) {
												return $item['id'];
											} );
										}
									}

									return $properties;
								} ),
							];
						} ),
					];
				} );
			}

			return $properties;
		} );
	}

	protected function get_visit_stats(): Base_Data_Type {
		return Tag::Object('Visit stats')->properties( function() {
			return [
				'views' => Tag::Object('Views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return \Voxel\Stats\get_sitewide_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return \Voxel\Stats\get_sitewide_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return \Voxel\Stats\get_sitewide_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return \Voxel\Stats\get_sitewide_views('all');
						} ),
					];
				} ),
				'unique_views' => Tag::Object('Unique views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return \Voxel\Stats\get_sitewide_unique_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return \Voxel\Stats\get_sitewide_unique_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return \Voxel\Stats\get_sitewide_unique_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return \Voxel\Stats\get_sitewide_unique_views('all');
						} ),
					];
				} ),
				'countries' => Tag::Object_List('Top countries')->items( function() {
					return \Voxel\Stats\get_sitewide_tracking_stats('countries');
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
					return \Voxel\Stats\get_sitewide_tracking_stats('ref_domains');
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
					return \Voxel\Stats\get_sitewide_tracking_stats('ref_urls');
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
					return \Voxel\Stats\get_sitewide_tracking_stats('browsers');
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
					return \Voxel\Stats\get_sitewide_tracking_stats('platforms');
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
					return \Voxel\Stats\get_sitewide_tracking_stats('devices');
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
					$time = \Voxel\Stats\get_sitewide_last_updated_time();
					if ( $time === null ) {
						return '';
					}

					return date( 'Y-m-d H:i:s', $time );
				} ),
			];
		} );
	}

	public function methods(): array {
		return [
			'query_var' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\Site_Query_Var_Method::class,
			'math' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\Site_Math_Method::class,
		];
	}
}
