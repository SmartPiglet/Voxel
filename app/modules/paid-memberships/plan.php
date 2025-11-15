<?php

namespace Voxel\Modules\Paid_Memberships;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan {

	protected
		$key,
		$label,
		$description,
		$pricing,
		$submissions,
		$archived,
		$settings,
		$prices;

	protected static $instances = [];

	public static function get( $key ) {
		if ( is_array( $key ) ) {
			$key = $key['key'] ?? null;
		}

		$plans = \Voxel\get( 'plans', [] );
		if ( ! isset( $plans[ $key ] ) ) {
			if ( $key === 'default' ) {
				return static::get_or_create_default_plan();
			}

			return null;
		}

		if ( ! array_key_exists( $key, static::$instances ) ) {
			static::$instances[ $key ] = new static( $plans[ $key ] );
		}

		return static::$instances[ $key ];
	}

	/**
	 * Get a list of all created membership plans on the site.
	 *
	 * @since 1.0
	 */
	public static function all(): array {
		return array_filter( array_map(
			'\Voxel\Modules\Paid_Memberships\Plan::get',
			\Voxel\get( 'plans', [] )
		) );
	}

	/**
	 * Get a list of all active plans on the site.
	 *
	 * @since 1.0
	 */
	public static function active(): array {
		return array_filter( static::all(), function( $plan ) {
			return ! $plan->is_archived();
		} );
	}

	/**
	 * Get a list of all archived plans on the site.
	 *
	 * @since 1.0
	 */
	public static function archived(): array {
		return array_filter( static::all(), function( $plan ) {
			return $plan->is_archived();
		} );
	}

	/**
	 * Get (or create if missing) the default/fallback plan.
	 * This plan should always exist.
	 *
	 * @since 1.0
	 */
	public static function get_or_create_default_plan() {
		$plans = \Voxel\get( 'plans', [] );
		if ( isset( $plans['default'] ) ) {
			return static::get( $plans['default'] );
		}

		$default = [
			'key' => 'default',
			'label' => 'Free plan',
			'description' => null,
			'pricing' => [],
			'submissions' => [],
			'archived' => false,
			'prices' => [],
		];

		\Voxel\set( 'plans', array_merge( [ 'default' => $default ], $plans ) );
		return static::get( $default );
	}

	public static function create( array $data, $is_update = false ): \Voxel\Modules\Paid_Memberships\Plan {
		$plans = \Voxel\get( 'plans', [] );
		$data = array_merge( [
			'key' => null,
			'label' => null,
			'description' => null,
			'pricing' => [],
			'submissions' => [],
			'archived' => false,
			'settings' => [],
			'prices' => [],
		], $data );

		if ( empty( $data['key'] ) || ( ! $is_update && isset( $plans[ $data['key'] ] ) ) ) {
			throw new \Exception( _x( 'Please provide a unique key.', 'membership plans', 'voxel-backend' ) );
		}

		if ( empty( $data['label'] ) ) {
			throw new \Exception( _x( 'Please provide a label.', 'membership plans', 'voxel-backend' ) );
		}

		$plans[ $data['key'] ] = [
			'key' => $data['key'],
			'label' => $data['label'],
			'description' => $data['description'],
			'pricing' => $data['pricing'],
			'submissions' => $data['submissions'],
			'archived' => !! $data['archived'],
			'settings' => $data['settings'],
			'prices' => $data['prices'],
		];

		\Voxel\set( 'plans', $plans );
		return static::get( $data['key'] );
	}

	public function update( $data_or_key, $value = null ) {
		$data = $this->get_config();

		if ( is_array( $data_or_key ) ) {
			$data = array_merge( $data, $data_or_key );
		} else {
			$data[ $data_or_key ] = $value;
		}

		$data['key'] = $this->key;
		static::create( $data, $is_update = true );

		$this->label = $data['label'] ?? $this->label;
		$this->description = $data['description'] ?? $this->description;
		$this->submissions = $data['submissions'] ?? $this->submissions;
		$this->archived = $data['archived'] ?? $this->archived;
		$this->pricing = $data['pricing'] ?? $this->pricing;
		$this->settings = $data['settings'] ?? $this->settings;
		$this->prices = $data['prices'] ?? $this->prices;
	}

	public function get_key() {
		return $this->key;
	}

	public function get_label() {
		return $this->label;
	}

	public function get_description() {
		return $this->description;
	}

	public function is_archived() {
		return $this->archived;
	}

	public function get_pricing() {
		return $this->pricing;
	}

	protected function __construct( $data ) {
		$this->key = $data['key'];
		$this->label = $data['label'];
		$this->description = $data['description'];
		$this->submissions = $data['submissions'];
		$this->archived = !! ( $data['archived'] ?? false );
		$this->settings = (array) ( $data['settings'] ?? [] );
		$this->pricing = [
			'live' => $data['pricing']['live'] ?? null,
			'test' => $data['pricing']['test'] ?? null,
		];
		$this->prices = (array) ( $data['prices'] ?? [] );
	}

	/**
	 * Check whether the given user role is allowed to switch to this plan.
	 *
	 * @since 1.2
	 */
	public function supports_role( $role_key ): bool {
		// fallback plan supports all roles
		if ( $this->get_key() === 'default' ) {
			return true;
		}

		if ( $this->config( 'settings.supported_roles' ) === 'all' ) {
			return true;
		} elseif ( $this->config( 'settings.supported_roles' ) === 'custom' ) {
			return in_array( $role_key, $this->config( 'settings.supported_roles_custom' ), true );
		} else {
			return false;
		}
	}

	/**
	 * Check whether the given user is allowed to switch to this plan. User should only be
	 * allowed to switch if they have at least one role that supports the plan.
	 *
	 * @since 1.2
	 */
	public function supports_user( \Voxel\User $user ): bool {
		// fallback plan supports all roles
		if ( $this->get_key() === 'default' ) {
			return true;
		}

		foreach ( $user->get_role_keys() as $role_key ) {
			if ( $this->supports_role( $role_key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the configuration array for this plan as stored in the database,
	 * validated to the expected schema.
	 *
	 * @since 1.0
	 */
	public function get_config() {
		return [
			'key' => $this->key,
			'label' => $this->label,
			'description' => $this->description,
			'pricing' => $this->pricing,
			'prices' => $this->prices,
			'submissions' => $this->submissions,
			'archived' => $this->archived,
			'settings' => [
				'supported_roles' => $this->settings['supported_roles'] ?? 'all', // all|custom
				'supported_roles_custom' => (array) ( $this->settings['supported_roles_custom'] ?? [] ),
			],
		];
	}

	/**
	 * Access helper for the plan configuration array, supporting dot notation.
	 *
	 * @since 1.0
	 */
	public function config( $setting_key = null, $default = null ) {
		$config = $this->get_config();

		if ( $setting_key !== null ) {
			$keys = explode( '.', $setting_key );
			foreach ( $keys as $key ) {
				if ( ! isset( $config[ $key ] ) ) {
					return $default;
				}

				$config = $config[ $key ];
			}
		}

		return $config;
	}

	/**
	 * Prepare configuration object for the backend plan editor screen.
	 *
	 * @since 1.0
	 */
	public function get_editor_config() {
		$config = $this->get_config();

		foreach ( [ 'live', 'test' ] as $mode ) {
			$pricing = $config['pricing'][ $mode ] ?? [
				'product_id' => null,
				'prices' => [],
			];

			foreach ( (array) ( $pricing['prices'] ?? [] ) as $price_id => $price ) {
				$pricing['prices'][ $price_id ]['id'] = $price_id;
				$pricing['prices'][ $price_id ]['is_zero_decimal'] = \Voxel\Utils\Currency_List::is_zero_decimal( strtoupper( $price['currency'] ) );
				$pricing['prices'][ $price_id ]['tax_behavior'] = $price['tax_behavior'] ?? 'n/a';
			}

			$pricing['prices'] = array_values( (array) ( $pricing['prices'] ?? [] ) );

			$config['pricing'][ $mode ] = $pricing;
		}

		return $config;
	}

	/**
	 * Get link to the backend plan editor screen for this plan.
	 *
	 * @since 1.0
	 */
	public function get_edit_link() {
		return admin_url( sprintf( 'admin.php?page=voxel-paid-members-plans&action=edit-plan&plan=%s', $this->get_key() ) );
	}
}
