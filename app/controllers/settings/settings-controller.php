<?php

namespace Voxel\Controllers\Settings;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 11 );
		$this->on( 'admin_post_voxel_save_general_settings', '@save_general_settings' );
		$this->on( 'admin_post_voxel_save_timeline_settings', '@save_timeline_settings' );
	}

	protected function add_menu_page() {
		add_menu_page(
			__( 'Voxel settings', 'voxel-backend' ),
			__( 'Voxel', 'voxel-backend' ),
			'manage_options',
			'voxel-settings',
			function() {
				$schema = static::get_settings_schema();
				$schema->set_value( \Voxel\get( 'settings', [] ) );
				$config = $schema->export();

				$config['tab'] = $_GET['tab'] ?? 'addons';
				$config['editor'] = [
					'share' => [
						'presets' => \Voxel\Utils\Sharer::get_links(),
					],
					'ipgeo' => [
						'providers' => \Voxel\get_ipgeo_providers(),
					],
				];

				require locate_template( 'templates/backend/general-settings/general-settings.php' );
			},
			\Voxel\get_image('post-types/logo.svg'),
			'0.207'
		);
	}

	protected function save_general_settings() {
		check_admin_referer( 'voxel_save_general_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['config'] ) ) {
			die;
		}

		$previous_config = \Voxel\get( 'settings', [] );
		$submitted_config = json_decode( stripslashes( $_POST['config'] ), true );

		$schema = static::get_settings_schema();
		$schema->set_value( $previous_config );

		foreach ( $submitted_config as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$config = $schema->export();

		\Voxel\set( 'settings', Schema::optimize_for_storage( $config ) );

		do_action( 'voxel/global-settings/saved', $config, $previous_config );

		wp_safe_redirect( add_query_arg( 'tab', $submitted_config['tab'] ?? null, admin_url( 'admin.php?page=voxel-settings' ) ) );
		die;
	}

	protected function save_timeline_settings() {
		check_admin_referer( 'voxel_save_timeline_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['config'] ) ) {
			die;
		}

		$previous_config = \Voxel\get( 'settings', [] );
		$submitted_config = json_decode( stripslashes( $_POST['config'] ), true );

		$schema = static::get_settings_schema();
		$schema->set_value( $previous_config );

		foreach ( $submitted_config as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$config = $schema->export();

		\Voxel\set( 'settings', Schema::optimize_for_storage( $config ) );

		wp_safe_redirect( add_query_arg( 'tab', $submitted_config['tab'] ?? null, admin_url( 'admin.php?page=voxel-timeline-settings' ) ) );
		die;
	}

	public static function get_settings_schema() {
		return Schema::Object( apply_filters( 'voxel/global-settings/register', [
			'addons' => Schema::Object( [
				'paid_memberships' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'collections' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'max_count' => Schema::Int()->min(0)->default(10),
				] ),
				'direct_messages' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
				'paid_listings' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),
			'recaptcha' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'key' => Schema::String(),
				'secret' => Schema::String(),
			] ),

			'auth' => Schema::Object( [
				'google' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'client_id' => Schema::String(),
					'client_secret' => Schema::String(),
				] ),
			] ),
			'db' => Schema::Object( [
				'type' => Schema::Enum( [
					'mysql',
					'mariadb',
				] )->default('mysql'),
				'max_revisions' => Schema::Int()->min(0)->default(5),
				'keyword_search' => Schema::Object( [
					'min_word_length' => Schema::Int()->min(0)->default(3),
					'stopwords' => Schema::String(),
				] ),
			] ),
			'notifications' => Schema::Object( [
				'admin_user' => Schema::Int()->min(0),
				'inapp_persist_days' => Schema::Int()->min(0)->default(30), // how many days to keep inapp notifications for
			] ),
			'messages' => Schema::Object( [
				'persist_days' => Schema::Int()->min(0)->default(365), // how many days to keep messages in the db
				'maxlength' => Schema::Int()->min(0)->default(2000),
				'files' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'max_count' => Schema::Int()->min(0)->default(1),
					'max_size' => Schema::Int()->min(0)->default(1000),
					'allowed_file_types' => Schema::List()
						->validator('is_string')
						->default( [
							'image/jpeg',
							'image/png',
							'image/webp',
						] ),
				] ),
				'enable_seen' => Schema::Bool()->default(true),
				'enable_real_time' => Schema::Bool()->default(true),
			] ),
			'emails' => Schema::Object( [
				'from_name' => Schema::String(),
				'from_email' => Schema::String(),
				'footer_text' => Schema::String(),
			] ),
			'nav_menus' => Schema::Object( [
				'custom_locations' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
				] )->default([]),
			] ),
			'icons' => Schema::Object( [
				'line_awesome' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
			] ),
			'share' => Schema::Object( [
				'networks' => Schema::Object_List( [
					'type' => Schema::String(),
					'key' => Schema::String(),
					'label' => Schema::String(),
					'icon' => Schema::String(),
				] )
				->validator( function( $item ) {
					return ( $item['type'] ?? null ) !== 'ui-heading';
				} )
				->default( \Voxel\Utils\Sharer::get_default_config() ),
			] ),
			'stats' => Schema::Object( [
				'enabled_post_types' => Schema::List()
					->validator('is_string')
					->default([]),
				'db_ttl' => Schema::Int()->min(0)->default(90), // number of days to persist visits in database for
				'cache_ttl' => Schema::Object( [
					'value' => Schema::Float()->default(24),
					'unit' => Schema::Enum( [
						'minutes',
						'hours',
						'days',
					] )->default('hours'),
				] ),
			] ),
			'ipgeo' => Schema::Object( [
				'providers' => Schema::Object_List( [
					'key' => Schema::String(),
					'api_key' => Schema::String(),
				] )->default([]),
			] ),
			'perf' => Schema::Object( [
				'user_scalable' => Schema::Enum( [
					'yes',
					'no',
					'auto',
				] )->default('no'),
			] ),
			'product_types' => Schema::Object( [
				'enabled' => Schema::Bool()->default(true),
			] ),
			'membership' => Schema::Object( [
				'require_verification' => Schema::Bool()->default(true),
				'username_behavior' => Schema::Enum( [
					'display_as_field',
					'generate_from_email',
				] )->default( 'display_as_field' ),
			] ),
		] ) );
	}
}
