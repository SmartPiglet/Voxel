<?php

namespace Voxel\Modules\Paid_Listings;

use Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Listing_Plan {

	protected static $instances = [];

	public static function get( $key ): ?static {
		if ( ! is_string( $key ) ) {
			return null;
		}

		$plans = (array) \Voxel\get( 'paid_listings.plans', [] );
		if ( ! isset( $plans[ $key ] ) ) {
			return null;
		}

		if ( ! array_key_exists( $key, static::$instances ) ) {
			static::$instances[ $key ] = new static( (array) $plans[ $key ] );
		}

		return static::$instances[ $key ];
	}

	public static function all(): array {
		return array_filter( array_map(
			'\Voxel\Modules\Paid_Listings\Listing_Plan::get',
			array_keys( (array) \Voxel\get( 'paid_listings.plans', [] ) )
		) );
	}

	public static function get_settings_schema(): Data_Object {
		return Schema::Object( [
			'key' => Schema::String(),
			'label' => Schema::String()->default(''),
			'description' => Schema::String()->default(''),
			// 'category' => Schema::String(),
			'limits' => Schema::Object_List( [
				'total' => Schema::Int()->min(0),
				'post_types' => Schema::List()
					->validator( function( $item ) {
						$post_type = \Voxel\Post_Type::get( $item );
						return $post_type && $post_type->is_managed_by_voxel();
					} )
					->unique()
					->default([]),
				'mark_verified' => Schema::Bool()->default(false),
				'priority' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'value' => Schema::Int(),
				] ),
				'expiration' => Schema::Object( [
					'mode' => Schema::Enum( [ 'fixed_days', 'auto' ] )->default('auto'),
					'fixed_days' => Schema::Int(),
				] ),
			] )->default([]),
		'billing' => Schema::Object( [
			'mode' => Schema::Enum( [ 'payment', 'subscription' ] )->default('payment'),
			'amount' => Schema::Float()->min(0)->default(0),
			'discount_amount' => Schema::Float()->min(0),
			'interval' => Schema::Enum( [ 'day', 'week', 'month', 'year' ] )->default('month'),
			'frequency' => Schema::Int()->min(1)->default(1),
			'disable_repeat_purchase' => Schema::Bool()->default(false),
			'restore_slot_on_delete' => Schema::Bool()->default(false),
		] ),
		] );
	}

	protected $config;

	protected function __construct( array $config ) {
		$this->config = $config;
	}

	public function get_edit_link() {
		return admin_url( sprintf( 'admin.php?page=voxel-paid-listings-plans&action=edit-plan&plan=%s', $this->get_key() ) );
	}

	public static function create( array $data, bool $is_update = false ): static {
		$plans = \Voxel\get( 'paid_listings.plans', [] );

		$schema = static::get_settings_schema();
		$schema->set_value( $data );

		$value = $schema->export();

		if ( empty( $value['key'] ) || ( ! $is_update && isset( $plans[ $value['key'] ] ) ) ) {
			throw new \Exception( _x( 'Please provide a unique key.', 'listing plans', 'voxel-backend' ) );
		}

		if ( empty( $value['label'] ) ) {
			throw new \Exception( _x( 'Please provide a label.', 'listing plans', 'voxel-backend' ) );
		}

		$plans[ $value['key'] ] = Schema::optimize_for_storage( $value );

		\Voxel\set( 'paid_listings.plans', $plans );

		return static::get( $value['key'] );
	}

	public function update( array $data ) {
		unset( $data['key'] ); // can't be modified

		$schema = static::get_settings_schema();
		$schema->set_value( $this->config );

		foreach ( $data as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$value = $schema->export();

		static::create( $value, is_update: true );
		$this->config = $value;
	}

	public function delete() {
		$plans = \Voxel\get( 'paid_listings.plans', [] );
		unset( $plans[ $this->get_key() ] );
		\Voxel\set( 'paid_listings.plans', $plans );
	}

	protected $config_schema_cache;
	public function config( $option, $default = null ) {
		if ( $this->config_schema_cache === null ) {
			$this->config_schema_cache = static::get_settings_schema();
		}

		$path = explode( '.', $option );

		$schema_item = $this->config_schema_cache;
		foreach ( $path as $item_key ) {
			if ( ! $schema_item instanceof Data_Object ) {
				return $default;
			}

			$schema_item = $schema_item->get_prop( $item_key );
		}

		if ( $schema_item === null ) {
			return $default;
		}

		if ( $schema_item->get_meta('exported') === true ) {
			return $schema_item->get_meta('exported_value') ?? $default;
		}

		$config = $this->config;
		foreach ( $path as $item_key ) {
			if ( ! isset( $config[ $item_key ] ) ) {
				$config = $default;
				break;
			}

			$config = $config[ $item_key ];
		}

		$schema_item->set_value( $config );
		$value = $schema_item->export();
		$schema_item->set_meta('exported', true);
		$schema_item->set_meta('exported_value', $value);

		return $value;
	}

	public function get_key() {
		return $this->config('key');
	}

	public function get_label(): string {
		return $this->config('label');
	}

	public function get_description(): string {
		return $this->config('description');
	}

	public function get_billing_mode() {
		return $this->config('billing.mode');
	}

	public function get_billing_amount() {
		return $this->config('billing.amount');
	}

	public function get_billing_discount_amount() {
		return $this->config('billing.discount_amount');
	}

	public function get_billing_interval() {
		return $this->config('billing.interval');
	}

	public function get_billing_frequency() {
		return $this->config('billing.frequency');
	}

	public function get_limits(): array {
		$limits = [];
		$used_post_types = [];

		foreach ( $this->config('limits') as $limit ) {
			if ( $limit['total'] === null || $limit['total'] <= 0 ) {
				continue;
			}

			$limit['post_types'] = array_filter( $limit['post_types'], function( $post_type ) use ( &$used_post_types ) {
				if ( post_type_exists( $post_type ) && ! isset( $used_post_types[ $post_type ] ) ) {
					$used_post_types[ $post_type ] = true;
					return true;
				} else {
					return false;
				}
			} );

			if ( empty( $limit['post_types'] ) ) {
				continue;
			}

			if ( $this->get_billing_mode() !== 'payment' ) {
				$limit['expiration'] = [
					'mode' => 'auto',
				];
			}

			$limits[] = $limit;
		}

		return $limits;
	}

	public function get_supported_post_types(): array {
		$post_types = [];
		foreach ( $this->get_limits() as $limit ) {
			foreach ( $limit['post_types'] as $post_type ) {
				$post_types[ $post_type ] = true;
			}
		}

		return array_keys( $post_types );
	}

	public function supports_post_type( \Voxel\Post_Type|string $post_type ): bool {
		$post_type_key = is_string( $post_type ) ? $post_type : $post_type->get_key();
		foreach ( $this->get_limits() as $limit ) {
			if ( in_array( $post_type_key, $limit['post_types'], true ) ) {
				return true;
			}
		}

		return false;
	}

	public function get_editor_config(): array {
		$schema = static::get_settings_schema();
		$schema->set_value( $this->config );

		return $schema->export();
	}

	public function get_product(): \Voxel\Post {
		$product = \Voxel\Post::find( [
			'post_type' => '_vx_catalog',
			'post_status' => 'publish',
			'meta_query' => [
				[
					'key' => '_vx_catalog_category',
					'value' => 'paid_listings_plan',
				],
				[
					'key' => '_vx_plan_key',
					'value' => $this->get_key(),
				],
			],
		] );

		if ( ! $product ) {
			$product_id = wp_insert_post( [
				'post_type' => '_vx_catalog',
				'post_status' => 'publish',
				'post_title' => $this->get_label(),
				'post_author' => \Voxel\get_main_admin()?->get_id(),
				'meta_input' => [
					'_vx_catalog_category' => 'paid_listings_plan',
					'_vx_plan_key' => $this->get_key(),
				],
			] );

			$product = \Voxel\Post::get( $product_id );
		}

		if ( $product->get_title() !== $this->get_label() || ! $product->get_author() ) {
			wp_update_post( [
				'ID' => $product->get_id(),
				'post_title' => $this->get_label(),
				'post_author' => \Voxel\get_main_admin()?->get_id(),
			] );

			$product = \Voxel\Post::force_get( $product->get_id() );
		}

		return $product;
	}

	public function get_product_id(): int {
		return $this->get_product()->get_id();
	}

}
