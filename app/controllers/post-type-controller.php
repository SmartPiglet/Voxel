<?php

namespace Voxel\Controllers;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Controller extends Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/backend/post-types/screen:edit-type', '@render_edit_screen', 30 );
		$this->on( 'admin_post_voxel_save_post_type_settings', '@save_settings' );
		$this->on( 'voxel_ajax_pte.export_revision', '@export_revision' );
		$this->on( 'voxel_ajax_pte.rollback_revision', '@rollback_revision' );
		$this->on( 'voxel_ajax_pte.remove_revision', '@remove_revision' );
		$this->on( 'voxel_ajax_pte.import_config', '@import_config' );
	}

	protected function render_edit_screen() {
		$key = $_GET['post_type'] ?? null;
		$post_type = \Voxel\Post_Type::get( $key );
		if ( ! ( $key && $post_type ) ) {
			return;
		}

		// create templates for post type if they don't exist
		$post_type->get_templates( $create_if_not_exists = [ 'single', 'card', 'archive' ] );

		// export dynamic data group
		$exporter = \Voxel\Dynamic_Data\Exporter::get();
		$exporter->add_group_by_key( 'post', $post_type->get_key() );

		// load required assets
		wp_enqueue_script('vue');
		wp_enqueue_script('sortable');
		wp_enqueue_script('vue-draggable');
		wp_enqueue_script('vx:post-type-editor.js');

		$auto_index = false;
		$indexing = (array) json_decode( get_option(
			sprintf( 'post_type_index:%s', $post_type->get_key() )
		), ARRAY_A );
		if ( in_array( $indexing['status'] ?? null, [ 'needs-processing', 'batch-processed' ], true ) ) {
			$auto_index = true;
		}

		$editor_options = [
			'elementor_edit_link' => admin_url( 'post.php?post={id}&action=elementor' ),
			'elementor_preview_link' => home_url( '/?p={id}' ),
			'nonces' => [
				'templates' => wp_create_nonce( 'vx_admin_edit_templates' ),
			],
			'field_types' => [],
			'field_presets' => $this->get_field_presets( $post_type ),
			'supported_conditions' => [],
			'filter_supported_conditions' => [],
			'filter_types' => [],
			'condition_types' => [],
			'filter_condition_types' => [],
			'orderby_types' => [],
			'orderby_type_labels' => [],
			'orderby_presets' => $this->get_orderby_presets( $post_type ),
			'auto_index' => $auto_index,
			'repeatable' => [],
			'singular' => [],
			'is_ui' => [],
			'has_overridable_models' => [],
			'overridable_models' => [],
			'permalink_front' => \Voxel\get_permalink_front(),
			'revisions' => array_filter( array_map( function( $revision ) {
				if ( empty( $revision['time'] ) ) {
					return null;
				}

				$author = \Voxel\User::get( $revision['author'] ?? '' );
				return [
					'timestamp' => $revision['time'],
					'date' => \Voxel\datetime_format( $revision['time'] + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
					'author' => $author ? $author->get_display_name() : _x( '(unknown)', 'unknown revision author', 'voxel-backend' ),
				];
			}, $post_type->revisions->get_revisions() ) ),
		];

		// fields config
		ob_start();
		foreach ( \Voxel\config('post_types.field_types') as $field_type => $field_class ) {
			$field = new $field_class;
			$field->set_post_type( $post_type );

			printf( '<template v-if="field.type === \'%s\'">', $field->get_type() );
			foreach ( $field->get_models() as $model_key => $model_args ) {
				if ( is_callable( $model_args ) ) {
					$model_args();
				} else {
					$model_type = $model_args['type'];
					if ( ! isset( $model_args['v-model'] ) ) {
						$model_args['v-model'] = sprintf( 'field[%s]', wp_json_encode( $model_key ) );
					}
					unset( $model_args['type'] );
					$model_type::render( $model_args );
				}
			}

			printf( '</template>' );

			$editor_options['field_types'][ $field->get_type() ] = (object) $field->get_props();
			$editor_options['supported_conditions'][ $field->get_type() ] = $field->get_supported_conditions();
			$editor_options['repeatable'][ $field->get_type() ] = $field::is_repeatable();
			$editor_options['singular'][ $field->get_type() ] = $field::is_singular();
			$editor_options['is_ui'][ $field->get_type() ] = $field->is_ui();

			$overridable_models = $field->get_overridable_models();
			if ( ! empty( $overridable_models ) ) {
				$editor_options['has_overridable_models'][ $field->get_type() ] = true;
				$editor_options['overridable_models'][ $field->get_type() ] = $overridable_models;
			}
		}
		$field_options_markup = ob_get_clean();

		// filters config
		ob_start();
		foreach ( \Voxel\config('post_types.filter_types') as $filter_type => $filter_class ) {
			$filter = new $filter_class;
			$filter->set_post_type( $post_type );

			printf( '<template v-if="filter.type === \'%s\'">', $filter->get_type() );
			foreach ( $filter->get_models() as $model_key => $model_args ) {
				if ( is_callable( $model_args ) ) {
					$model_args();
				} else {
					$model_type = $model_args['type'];
					$model_args['v-model'] = sprintf( 'filter[%s]', wp_json_encode( $model_key ) );
					unset( $model_args['type'] );
					$model_type::render( $model_args );
				}
			}
			printf( '</template>' );

			$editor_options['filter_types'][ $filter->get_type() ] = (object) $filter->get_props();
			$editor_options['filter_supported_conditions'][ $filter->get_type() ] = $filter->get_supported_conditions();
		}
		$filter_options_markup = ob_get_clean();

		// orderby config
		ob_start();
		foreach ( \Voxel\config('post_types.orderby_types') as $orderby_type => $orderby_class ) {
			$orderby = new $orderby_class;
			$orderby->set_post_type( $post_type );

			printf( '<template v-if="clause.type === \'%s\'">', $orderby->get_type() );
			foreach ( $orderby->get_models() as $model_key => $model_args ) {
				if ( is_callable( $model_args ) ) {
					$model_args();
				} else {
					$model_type = $model_args['type'];
					$model_args['v-model'] = sprintf( 'clause[%s]', wp_json_encode( $model_key ) );
					unset( $model_args['type'] );
					$model_type::render( $model_args );
				}
			}
			printf( '</template>' );

			$editor_options['orderby_types'][ $orderby->get_type() ] = (object) $orderby->get_props();
			$editor_options['orderby_type_labels'][ $orderby->get_type() ] = $orderby->get_label();
		}
		$orderby_options_markup = ob_get_clean();

		// conditions config
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

			$editor_options['condition_types'][ $condition->get_type() ] = [
				'props' => $condition->get_props(),
				'label' => $condition->get_label(),
				'type' => $condition->get_type(),
				'group' => $condition->get_group(),
			];
		}
		$condition_options_markup = ob_get_clean();

		// conditions config
		ob_start();
		foreach ( \Voxel\config('post_types.filter_condition_types') as $condition_type => $condition_class ) {
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

			$editor_options['filter_condition_types'][ $condition->get_type() ] = [
				'props' => $condition->get_props(),
				'label' => $condition->get_label(),
				'type' => $condition->get_type(),
				'group' => $condition->get_group(),
			];
		}
		$filter_condition_options_markup = ob_get_clean();

		$editor_options['product_types'] = [];
		foreach ( \Voxel\Product_Type::all() as $product_type ) {
			$editor_options['product_types'][ $product_type->get_key() ] = [
				'key' => $product_type->get_key(),
				'label' => $product_type->get_label(),
				'addons' => [],
			];

			if ( $product_type->config('modules.addons.enabled') ) {
				$editor_options['product_types'][ $product_type->get_key() ]['addons'] = array_map( function( $addon ) {
					return [
						'type' => $addon['type'],
						'key' => $addon['key'],
						'label' => $addon['label'],
					];
				}, $product_type->config('modules.addons.items') );
			}

			if ( $product_type->config('modules.stock.enabled') ) {
				$editor_options['product_types'][ $product_type->get_key() ]['stock_enabled'] = true;
			}
		}

		// dd($editor_options);

		// general editor config
		printf( '<script type="text/javascript">window.Post_Type_Options = %s;</script>', wp_json_encode( (object) $editor_options ) );

		// post type config
		printf(
			'<script type="text/javascript">window.Post_Type_Config = %s;</script>',
			wp_json_encode( (object) $post_type->repository->get_editor_config() )
		);

		// for dynamic content modal
		\Voxel\set_current_post( \Voxel\Post::dummy( [ 'post_type' => $post_type->get_key() ] ) );

		require locate_template( 'templates/backend/post-types/edit-post-type.php' );
	}

	protected function save_settings() {
		check_admin_referer( 'voxel_save_post_type_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['post_type_config'] ) ) {
			die;
		}

		$config = json_decode( stripslashes( $_POST['post_type_config'] ), true );
		$settings = $config['settings'];
		$post_type = \Voxel\Post_Type::get( $settings['key'] );
		if ( ! ( $settings['key'] && $post_type && json_last_error() === JSON_ERROR_NONE ) ) {
			die;
		}

		// delete post type
		if ( ! empty( $_POST['remove_post_type'] ) && $_POST['remove_post_type'] === 'yes' ) {
			$post_type->repository->remove();

			wp_safe_redirect( admin_url( 'admin.php?page=voxel-post-types' ) );
			die;
		}

		$previous_index_table_sql = $post_type->get_index_table()->get_sql();
		$previous_indexable_statuses = $post_type->repository->get_indexable_statuses();

		$previous_config = wp_json_encode( $post_type->repository->get_config() );

		$field_types = \Voxel\config('post_types.field_types');
		foreach ( $config['fields'] as $field_index => $field_config ) {
			if ( isset( $field_types[ $field_config['type'] ] ) ) {
				$field_type = new $field_types[ $field_config['type'] ];
				$config['fields'][ $field_index ] = $field_type->sanitize_in_editor( $field_config );
				$default_props = $field_type->get_props();
				unset( $default_props['type'], $default_props['key'] );

				// if a prop has the same value as the default value, don't store it in the db since it's unnecessary
				foreach ( $default_props as $prop_key => $prop_value ) {
					if ( isset( $field_config[ $prop_key ] ) && $field_config[ $prop_key ] === $prop_value ) {
						unset( $config['fields'][ $field_index ][ $prop_key ] );
					}
				}
			}
		}
		
		// edit post type
		$post_type->repository->set_config( [
			'settings' => $config['settings'],
			'fields' => $config['fields'],
			'search' => $config['search'],
			'templates' => $config['templates'],
			'custom_templates' => array_map( 'array_values', (array) $config['custom_templates'] ),
		] );

		if ( $previous_config !== wp_json_encode( $post_type->repository->get_config() ) ) {
			$post_type->revisions->save_revision();
			// \Voxel\log('saved post type revision');
		}

		$post_type = \Voxel\Post_Type::force_get( $post_type->get_key() );
		$index_table = $post_type->get_index_table();
		$new_index_table_sql = $index_table->get_sql();
		$new_indexable_statuses = $post_type->repository->get_indexable_statuses();

		if ( ( $previous_index_table_sql !== $new_index_table_sql ) || ! $index_table->exists() || ( $previous_indexable_statuses != $new_indexable_statuses ) ) {
			$index_table->recreate();
			update_option( sprintf( 'post_type_index:%s', $post_type->get_key() ), wp_json_encode( [
				'status' => 'needs-processing',
				'offset' => 0,
			] ) );
		}

		wp_safe_redirect( add_query_arg( 'tab', $_POST['active_tab'] ?? null, $post_type->get_edit_link() ) );
		die;
	}

	private function get_orderby_presets( \Voxel\Post_Type $post_type ) {
		return [
			'latest' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'latest',
				'label' => 'Latest',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Date_Created_Order::preset(),
				],
			], $post_type ),

			'best-rated' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'best-rated',
				'label' => 'Best rated',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Rating_Order::preset(),
				],
			], $post_type ),

			'relevance' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'relevance',
				'label' => 'Relevant',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Relevance_Order::preset( [ 'source' => 'keywords' ] ),
				],
			], $post_type ),

			'nearby' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'nearby',
				'label' => 'Nearby',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Nearby_Order::preset( [ 'source' => 'location' ] ),
				],
			], $post_type ),

			'priority' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'priority',
				'label' => 'Priority',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Priority_Order::preset(),
				],
			], $post_type ),

			'alphabetical' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'alphabetical',
				'label' => 'Alphabetical',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Text_Field_Order::preset( [ 'source' => 'title' ] ),
				],
			], $post_type ),

			'random' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'random',
				'label' => 'Random',
				'clauses' => [
					\Voxel\Post_Types\Order_By\Random_Order::preset( [ 'seed' => 10800 ] ),
				],
			], $post_type ),
			'custom' => \Voxel\Post_Types\Order_By\Order_By_Group::preset( [
				'key' => 'custom',
				'label' => 'Custom order',
				'clauses' => [],
			], $post_type ),
		];
	}

	private function get_field_presets( $post_type ) {
		$presets = [
			\Voxel\Post_Types\Fields\Singular\Title_Field::preset(),
			\Voxel\Post_Types\Fields\Singular\Description_Field::preset(),
			\Voxel\Post_Types\Fields\Singular\Timezone_Field::preset(),
			\Voxel\Post_Types\Fields\Location_Field::preset( [
				'label' => 'Location',
				'key' => 'location',
			] ),
			\Voxel\Post_Types\Fields\Email_Field::preset( [
				'label' => 'Email',
				'key' => 'email',
			] ),
			\Voxel\Post_Types\Fields\Image_Field::preset( [
				'label' => 'Logo',
				'key' => 'logo',
				'max-count' => 1,
			] ),
			\Voxel\Post_Types\Fields\Image_Field::preset( [
				'label' => 'Cover image',
				'key' => 'cover',
				'max-count' => 1,
			] ),
			\Voxel\Post_Types\Fields\Image_Field::preset( [
				'label' => 'Gallery',
				'key' => 'gallery',
				'max-count' => 10,
			] ),
			\Voxel\Post_Types\Fields\Image_Field::preset( [
				'label' => 'Featured image',
				'key' => '_thumbnail_id',
				'max-count' => 1,
			] ),
			\Voxel\Post_Types\Fields\Url_Field::preset( [
				'label' => 'Website',
				'key' => 'website',
			] ),
			\Voxel\Post_Types\Fields\Phone_Field::preset( [
				'label' => 'Phone number',
				'key' => 'phone',
			] ),
			\Voxel\Post_Types\Fields\Recurring_Date_Field::preset( [
				'label' => 'Event date',
				'key' => 'event_date',
			] ),
			\Voxel\Post_Types\Fields\Work_Hours_Field::preset( [
				'label' => 'Work hours',
				'key' => 'work_hours',
			] ),
			\Voxel\Post_Types\Fields\Product_Field::preset( [
				'label' => 'Product',
				'key' => 'product',
			] ),
		];

		if ( $post_type->get_key() === 'profile' ) {
			$presets[] = \Voxel\Post_Types\Fields\Profile\Profile_Avatar_Field::preset();
			$presets[] = \Voxel\Post_Types\Fields\Profile\Profile_Name_Field::preset();
		}

		return $presets;
	}

	protected function export_revision() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Permission denied.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			$timestamp = sanitize_text_field( $_GET['timestamp'] ?? '' );

			if ( ! ( $post_type && $timestamp ) ) {
				throw new \Exception( __( 'Missing post type or timestamp.', 'voxel-backend' ) );
			}

			if ( $timestamp === 'current' ) {
				$config = $post_type->repository->get_config();
				$filename = sprintf( '%s(%s).json', $post_type->get_key(), date('Y-m-d') );
				header( 'Content-disposition: attachment; filename='.$filename );
				header( 'Content-type: application/json' );
				echo wp_json_encode( $config );
			} else {
				$revision = $post_type->revisions->get_revision( $timestamp );
				if ( ! $revision ) {
					throw new \Exception( __( 'Could not find requested revision.', 'voxel-backend' ) );
				}

				$filename = sprintf( '%s(%s).json', $post_type->get_key(), date( 'Y-m-d', $revision['time'] ) );
				header( 'Content-disposition: attachment; filename='.$filename );
				header( 'Content-type: application/json' );
				echo wp_json_encode( $revision['config'] );
			}
		} catch ( \Exception $e ) {
			return call_user_func( apply_filters( 'wp_die_handler', '_default_wp_die_handler' ), $e->getMessage(), '', [ 'back_link' => true ] );
		}
	}

	protected function rollback_revision() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Permission denied.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			$timestamp = sanitize_text_field( $_GET['timestamp'] ?? '' );

			if ( ! ( $post_type && $timestamp ) ) {
				throw new \Exception( __( 'Missing post type or timestamp.', 'voxel-backend' ) );
			}

			$revision = $post_type->revisions->get_revision( $timestamp );
			if ( ! $revision ) {
				throw new \Exception( __( 'Could not find requested revision.', 'voxel-backend' ) );
			}

			$config = $revision['config'] ?? [];

			$post_type->repository->set_config( [
				'settings' => $config['settings'] ?? [],
				'fields' => $config['fields'] ?? [],
				'search' => $config['search'] ?? [],
			] );

			wp_safe_redirect( add_query_arg( 'revision', 'rollback', $post_type->get_edit_link() ) );
			exit;
		} catch ( \Exception $e ) {
			return call_user_func( apply_filters( 'wp_die_handler', '_default_wp_die_handler' ), $e->getMessage(), '', [ 'back_link' => true ] );
		}
	}

	protected function remove_revision() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Permission denied.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			$timestamp = sanitize_text_field( $_GET['timestamp'] ?? '' );

			if ( ! ( $post_type && $timestamp ) ) {
				throw new \Exception( __( 'Missing post type or timestamp.', 'voxel-backend' ) );
			}

			$revision = $post_type->revisions->get_revision( $timestamp );
			if ( ! $revision ) {
				throw new \Exception( __( 'Could not find requested revision.', 'voxel-backend' ) );
			}

			$post_type->revisions->delete_revision( $revision['time'] );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function import_config() {
		try {
			if ( ! current_user_can('manage_options') ) {
				throw new \Exception( __( 'Permission denied.', 'voxel-backend' ) );
			}

			$post_type = \Voxel\Post_Type::get( $_POST['post_type'] ?? null );
			$config = json_decode( stripslashes( $_POST['config'] ), true );

			if ( ! ( $post_type && $config ) ) {
				throw new \Exception( __( 'Missing post type or timestamp.', 'voxel-backend' ) );
			}

			if ( ! isset( $config['settings'], $config['fields'], $config['search'] ) ) {
				throw new \Exception( __( 'Provided config file is not valid.', 'voxel-backend' ) );
			}

			$post_type->repository->set_config( [
				'settings' => (array) $config['settings'],
				'fields' => (array) $config['fields'],
				'search' => (array) $config['search'],
			] );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => add_query_arg( 'revision', 'import', $post_type->get_edit_link() ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
