<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Role {

	public $wp_role;

	private $config;

	private $fields;

	/**
	 * Store role instances.
	 *
	 * @since 1.0
	 */
	private static $instances = [];

	/**
	 * Get a role based on its key.
	 *
	 * @since 1.0
	 */
	public static function get( $key ) {
		if ( is_string( $key ) ) {
			$role = get_role( $key );
			if ( ! $role instanceof \WP_Role ) {
				return null;
			}
		} elseif ( $key instanceof \WP_Role ) {
			$role = $key;
		} else {
			return null;
		}

		if ( ! array_key_exists( $role->name, static::$instances ) ) {
			static::$instances[ $role->name ] = new static( $role );
		}

		return static::$instances[ $role->name ];
	}

	public static function get_all(): array {
		return array_filter( array_map(
			'\Voxel\Role::get',
			wp_roles()->role_objects
		) );
	}

	public static function get_roles_supporting_registration(): array {
		return array_filter( static::get_all(), function( $role ) {
			return $role->is_registration_enabled();
		} );
	}

	public static function get_voxel_roles(): array {
		return array_filter( static::get_all(), function( $role ) {
			return $role->is_managed_by_voxel();
		} );
	}

	public static function get_other_roles(): array {
		return array_filter( static::get_all(), function( $role ) {
			return ! $role->is_managed_by_voxel();
		} );
	}

	private function __construct( \WP_Role $wp_role ) {
		$this->wp_role = $wp_role;
		$roles = \Voxel\get( 'roles', [] );
		$this->config = $roles[ $this->wp_role->name ] ?? [];
	}

	public function is_managed_by_voxel(): bool {
		return ! empty( $this->config );
	}

	public function get_label(): string {
		if ( ! empty( $this->config['settings']['label'] ) ) {
			return $this->config['settings']['label'];
		}

		return wp_roles()->role_names[ $this->wp_role->name ] ?? '(unknown)';
	}

	public function get_key(): string {
		return $this->wp_role->name;
	}

	public function get_caps(): array {
		return $this->wp_role->capabilities;
	}

	public function get_edit_link(): string {
		return admin_url( sprintf( 'admin.php?page=voxel-roles&role=%s&action=edit-role', $this->get_key() ) );
	}

	public function is_registration_enabled(): bool {
		if ( ! $this->_is_safe_for_registration() ) {
			return false;
		}

		if ( ! $this->is_managed_by_voxel() ) {
			return false;
		}

		return (bool) ( $this->config['registration']['enabled'] ?? true );
	}

	public function is_switching_enabled(): bool {
		if ( ! $this->_is_safe_for_registration() ) {
			return false;
		}

		if ( ! $this->is_managed_by_voxel() ) {
			return false;
		}

		return (bool) ( $this->config['settings']['role_switch']['enabled'] ?? true );
	}

	public function is_social_login_allowed(): bool {
		return (bool) ( $this->config['registration']['allow_social_login'] ?? true );
	}

	public function is_verification_required(): bool {
		return !! \Voxel\get( 'settings.membership.require_verification', true );
	}

	public function _is_safe_for_registration(): bool {
		// hard restricted
		if ( $this->get_key() === 'administrator' || $this->get_key() === 'editor' ) {
			return false;
		}

		$caps = $this->get_caps();
		$is_safe = true;

		if ( in_array( $this->get_key(), [ 'administrator', 'editor', 'author', 'contributor' ], true ) ) {
			$is_safe = false;
		}

		foreach ( static::get_unsafe_caps() as $cap ) {
			if ( ! empty( $caps[ $cap ] ) ) {
				$is_safe = false;
			}
		}

		return apply_filters( "voxel/roles/{$this->get_key()}/is_safe_for_registration", $is_safe, $this );
	}

	public function has_plans_enabled(): bool {
		return (bool) ( $this->config['registration']['plans_enabled'] ?? true );
	}

	public function get_pricing_page_id() {
		return $this->config['settings']['templates']['pricing'] ?? null;
	}

	/**
	 * Save configuration to database.
	 *
	 * @since 1.2
	 */
	public function set_config( $new_config ) {
		$roles = \Voxel\get( 'roles', [] );

		if ( isset( $new_config['settings'] ) ) {
			$this->config['settings'] = $new_config['settings'];
		}

		if ( isset( $new_config['registration'] ) ) {
			$this->config['registration'] = $new_config['registration'];
		}

		$valid_groups = [
			'settings' => true,
			'registration' => true,
		];

		foreach( $this->config as $key => $setting_group ) {
			if ( ! isset( $valid_groups[ $key ] ) ) {
				unset( $this->config[ $key ] );
			}
		}

		$roles[ $this->get_key() ] = $this->config;

		// cleanup roles array
		foreach ( $roles as $role_key => $role_config ) {
			if ( ! is_string( $role_key ) || empty( $role_key ) || empty( $role_config ) || empty( $role_config['settings']['key'] ) ) {
				unset( $roles[ $role_key ] );
			}
		}

		\Voxel\set( 'roles', $roles );
	}

	public function get_fields() {
		if ( is_array( $this->fields ) ) {
			return $this->fields;
		}

		$this->fields = [];
		$profile = \Voxel\Post_Type::get('profile');
		$available_fields = static::get_available_profile_fields();

		foreach ( ( $this->config['registration']['fields'] ?? [] ) as $field_data ) {
			if ( ! is_array( $field_data ) || empty( $field_data['source'] ) || empty( $field_data['key'] ) ) {
				continue;
			}

			if ( $field_data['source'] === 'profile' ) {
				if ( ! isset( $available_fields[ $field_data['key'] ] ) ) {
					continue;
				}

				$field = $available_fields[ $field_data['key'] ];

				if ( ! empty( $field_data['label'] ) ) {
					$field->set_prop( 'label', $field_data['label'] );
				}

				if ( ! empty( $field_data['enable-conditions'] ) ) {
					$field->set_prop( 'enable-conditions', $field_data['enable-conditions'] );
				}

				if ( ! empty( $field_data['conditions_behavior'] ) ) {
					$field->set_prop( 'conditions_behavior', $field_data['conditions_behavior'] );
				}

				if ( ! empty( $field_data['conditions'] ) ) {
					$field->set_prop( 'conditions', $field_data['conditions'] );
				}

				if ( ! empty( $field_data['description'] ) ) {
					$field->set_prop( 'description', $field_data['description'] );
				}

				if ( ! empty( $field_data['placeholder'] ) ) {
					$field->set_prop( 'placeholder', $field_data['placeholder'] );
				}

				$this->fields[ $field->get_key() ] = $field;
			}

			if ( $field_data['source'] === 'auth' ) {
				if ( \Voxel\get( 'settings.membership.username_behavior' ) !== 'generate_from_email' ) {
					if ( $field_data['key'] === 'voxel:auth-username' ) {
						$this->fields[ 'voxel:auth-username' ] = new \Voxel\Users\Registration_Fields\Username_Field( $field_data );
					}
				}

				if ( $field_data['key'] === 'voxel:auth-email' ) {
					$this->fields[ 'voxel:auth-email' ] = new \Voxel\Users\Registration_Fields\Email_Field( $field_data );
				}

				if ( $field_data['key'] === 'voxel:auth-password' ) {
					$this->fields[ 'voxel:auth-password' ] = new \Voxel\Users\Registration_Fields\Password_Field( $field_data );
				}
			}
		}

		if ( \Voxel\get( 'settings.membership.username_behavior' ) !== 'generate_from_email' ) {
			if ( ! ( $this->fields[ 'voxel:auth-username'] ?? null ) instanceof \Voxel\Users\Registration_Fields\Username_Field ) {
				$this->fields[ 'voxel:auth-username'] = new \Voxel\Users\Registration_Fields\Username_Field;
			}
		}

		foreach ( [
			'voxel:auth-email' => \Voxel\Users\Registration_Fields\Email_Field::class,
			'voxel:auth-password' => \Voxel\Users\Registration_Fields\Password_Field::class,
		] as $auth_field_key => $auth_field_class ) {
			if ( ! ( $this->fields[ $auth_field_key ] ?? null ) instanceof $auth_field_class ) {
				$this->fields[ $auth_field_key ] = new $auth_field_class;
			}
		}

		return $this->fields;
	}

	public function config( $setting_key = null, $default = null ) {
		$config = $this->config;

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

	public function get_editor_config(): array {
		return [
			'settings' => [
				'key' => $this->get_key(),
				'label' => $this->get_label(),
				'role_switch' => [
					'enabled' => $this->config['settings']['role_switch']['enabled'] ?? true,
					'show_plans_on_switch' => $this->config['settings']['role_switch']['show_plans_on_switch'] ?? true,
				],
				'templates' => [
					'pricing' => $this->get_pricing_page_id(),
				],
			],
			'registration' => [
				'enabled' => $this->config['registration']['enabled'] ?? true,
				'plans_enabled' => $this->config['registration']['plans_enabled'] ?? true,
				'allow_social_login' => $this->config['registration']['allow_social_login'] ?? true,
				'fields' => array_values( array_map( [ static::class, 'get_field_editor_config' ], $this->get_fields() ) ),
				'show_plans_on_signup' => $this->config['registration']['show_plans_on_signup'] ?? true,
				'after_registration' => $this->config['registration']['after_registration'] ?? 'welcome_step',
				'custom_redirect' => $this->config['registration']['custom_redirect'] ?? '',
			],
		];
	}

	public static function get_available_profile_fields() {
		return array_filter( \Voxel\Post_Type::get('profile')->get_fields(), function( $field ) {
			return in_array( $field->get_type(), [
				'text',
				'textarea',
				'number',
				'switcher',
				'phone',
				'url',
				'email',
				'taxonomy',
				'file',
				'image',
				'date',
				'title',
				'description',
				'profile-avatar',
				'profile-name',
				'select',
				'multiselect',
			], true );
		} );
	}

	public static function get_field_editor_config( $field ) {
		if ( $field instanceof \Voxel\Users\Registration_Fields\Base_Registration_Field ) {
			return [
				'source' => 'auth',
				'key' => $field->get_key(),
				'label' => $field->get_prop('label'),
				'description' => $field->get_prop('description'),
				'placeholder' => $field->get_prop('placeholder'),
			];
		} else {
			return [
				'source' => 'profile',
				'key' => $field->get_key(),
				'label' => $field->get_prop('label'),
				'description' => $field->get_prop('description'),
				'placeholder' => $field->get_prop('placeholder'),
				'enable-conditions' => $field->get_prop('enable-conditions'),
				'conditions' => $field->get_prop('conditions'),
				'conditions_behavior' => $field->get_prop('conditions_behavior'),
			];
		}
	}

	public static function get_unsafe_caps() {
		return [
			'manage_network',
			'manage_sites',
			'manage_options',
			'edit_others_posts',
			'edit_posts',
		];
	}
}
