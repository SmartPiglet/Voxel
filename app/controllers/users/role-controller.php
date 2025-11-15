<?php

namespace Voxel\Controllers\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Role_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 15 );
		$this->on( 'after_setup_theme', '@register_custom_roles' );
		$this->on( 'admin_post_voxel_create_membership_role', '@create_custom_role' );
		$this->on( 'admin_post_voxel_update_membership_role', '@update_custom_role' );
	}

	protected function add_menu_page() {
		add_submenu_page(
			'voxel-post-types',
			__( 'User Roles', 'voxel-backend' ),
			__( 'User Roles', 'voxel-backend' ),
			'manage_options',
			'voxel-roles',
			function() {
				$action = sanitize_text_field( $_GET['action'] ?? 'manage-roles' );

				if ( $action === 'create-role' ) {
					require locate_template( 'templates/backend/roles/create-role.php' );
				} elseif ( $action === 'edit-role' ) {
					$role = \Voxel\Role::get( $_GET['role'] ?? '' );
					if ( ! ( $role && $role->_is_safe_for_registration() ) ) {
						if ( $role && ! in_array( $role->get_key(), [ 'administrator', 'editor' ], true ) ) {
							$caps = $role->get_caps();
							$unsafe_caps = array_filter( $role::get_unsafe_caps(), function( $cap ) use ( $caps ) {
								return ! empty( $caps[ $cap ] );
							} ); ?>
							<div class="x-container">
								<div class="x-row">
									<div class="x-col-12">
										<div class="ts-spacer"></div>
										<h1 style="margin-botton: 20px;"><?= $role->get_label() ?></h1>
									</div>
									<div class="x-col-7">
										<p>
											This role cannot be used for registration due to having potentially unsafe permissions, which could give users access to parts of the admin dashboard.<br><br>
											If you have intentionally assigned these permissions and are aware of their implications, you can bypass this rule using the snippet below:
										</p>
									</div>
									<div class="x-col-12">
										<pre class="ts-snippet"><span class="ts-blue">add_filter</span>( <span class="ts-green">'voxel/roles/<?= $role->get_key() ?>/is_safe_for_registration'</span>, <span class="ts-green">'__return_true'</span> );</pre>
									</div>
									<?php if ( ! empty( $unsafe_caps ) ): ?>
										<div class="x-col-12">
											<details>
												<summary>Details</summary>
												<p>Unsafe capabilities detected: <?= join( ', ', $unsafe_caps ) ?></p>
											</details>
										</div>
									<?php endif ?>
								</div>
							</div>
						<?php }

						return;
					}

					$config = [
						'role' => $role->get_editor_config(),
						'available_fields' => array_map( function( $field ) {
							return [
								'source' => 'profile',
								'key' => $field->get_key(),
								'props' => [
									'type' => $field->get_type(),
									'label' => $field->get_label(),
									'description' => $field->get_description(),
									'placeholder' => $field->get_prop('placeholder'),
								],
							];
						}, \Voxel\Role::get_available_profile_fields() ),
						'supported_conditions' => $this->_editor_get_supported_conditions(),
						'condition_types' => $this->_editor_get_condition_types(),
					];

					$condition_options_markup = $this->_editor_get_condition_options_markup();

					wp_enqueue_script( 'vx:role-editor.js' );
					require locate_template( 'templates/backend/roles/edit-role.php' );
				} else {
					$voxel_roles = \Voxel\Role::get_voxel_roles();
					require locate_template( 'templates/backend/roles/manage-roles.php' );
				}
			},
			15
		);
	}

	protected function register_custom_roles() {
		$roles = (array) \Voxel\get('roles');
		foreach ( $roles as $role ) {
			if ( empty( $role['settings']['key'] ) ) {
				continue;
			}

			if ( ! get_role( $role['settings']['key'] ) ) {
				add_role( $role['settings']['key'], $role['settings']['label'] ?? '', [] );
			}
		}

		if ( ! isset( $roles['subscriber'] ) ) {
			$roles['subscriber'] = [
				'settings' => [
					'key' => 'subscriber',
					'label' => 'Subscriber',
					'templates' => [
						'pricing' => \Voxel\get( 'templates.pricing' ),
					],
				],
				'registration' => [
					'enabled' => \Voxel\get( 'settings.membership.enabled', true ),
					'plans_enabled' => \Voxel\get( 'settings.membership.plans_enabled' ),
					'after_registration' => \Voxel\get( 'settings.membership.after_registration' ),
					'show_plans_on_signup' => \Voxel\get( 'settings.membership.show_plans_on_signup' ),
				],
			];
			\Voxel\set( 'roles', $roles );
			\Voxel\set( 'templates.pricing', null );
		}
	}

	protected function create_custom_role() {
		check_admin_referer( 'voxel_manage_membership_roles' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		try {
			$key = sanitize_key( $_POST['role']['key'] ?? '' );
			$label = sanitize_text_field( $_POST['role']['label'] ?? '' );

			if ( empty( $key ) || empty( $label ) ) {
				throw new \Exception( _x( 'Role key and label are required.', 'create role', 'voxel-backend' ) );
			}

			if ( get_role( $key ) ) {
				throw new \Exception( _x( 'A role with this key already exists.', 'create role', 'voxel-backend' ) );
			}

			$roles = \Voxel\get( 'roles', [] );
			$roles[ $key ] = [
				'settings' => [
					'key' => $key,
					'label' => $label,
				],
			];

			\Voxel\set( 'roles', $roles );

			wp_safe_redirect( admin_url( 'admin.php?page=voxel-roles&action=edit&role='.$key ) );
			exit;
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage(), '', [ 'back_link' => true ] );
		}
	}

	protected function update_custom_role() {
		check_admin_referer( 'voxel_manage_membership_roles' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['role_config'] ) ) {
			die;
		}

		$config = json_decode( stripslashes( $_POST['role_config'] ), true );
		$role = \Voxel\Role::get( $config['settings']['key'] ?? '' );
		if ( ! ( $role && json_last_error() === JSON_ERROR_NONE ) ) {
			die;
		}

		// delete post type
		if ( ! empty( $_POST['remove_role'] ) && $_POST['remove_role'] === 'yes' ) {
			// remove from voxel:roles
			$roles = \Voxel\get( 'roles', [] );
			unset( $roles[ $role->get_key() ] );
			\Voxel\set( 'roles', $roles );

			// back to manage roles screen
			wp_safe_redirect( admin_url( 'admin.php?page=voxel-roles' ) );
			die;
		}

		$role->set_config( [
			'settings' => $config['settings'],
			'registration' => $config['registration'],
		] );

		wp_safe_redirect( admin_url( 'admin.php?page=voxel-roles&action=edit-role&role='.$role->get_key() ) );
		die;
	}

	protected function _editor_get_condition_options_markup() {
		ob_start();
		foreach ( \Voxel\config('post_types.condition_types') as $condition_type => $condition_class ) {
			$condition = new $condition_class;
			printf( '<template v-if="condition.type === \'%s\'">', $condition->get_type() );
			foreach ( $condition->get_models() as $model_key => $model_args ) {
				if ( is_callable( $model_args ) ) {
					$model_args();
				} else {
					$model_type = $model_args['type'];
					$model_args['v-model'] = sprintf( 'condition[%s]', wp_json_encode( $model_key ) );
					unset( $model_args['type'] );
					$model_type::render( $model_args );
				}
			}
			printf( '</template>' );
		}

		return ob_get_clean();
	}

	protected function _editor_get_condition_types() {
		$types = [];
		foreach ( \Voxel\config('post_types.condition_types') as $condition_class ) {
			$condition = new $condition_class;
			$types[ $condition->get_type() ] = [
				'props' => $condition->get_props(),
				'label' => $condition->get_label(),
				'type' => $condition->get_type(),
				'group' => $condition->get_group(),
			];
		}

		return $types;
	}

	protected function _editor_get_supported_conditions() {
		$conditions = [];
		foreach ( \Voxel\config('post_types.field_types') as $field_class ) {
			$field = new $field_class;
			$conditions[ $field->get_type() ] = $field->get_supported_conditions();
		}

		return $conditions;
	}
}
