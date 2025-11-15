<?php

namespace Voxel\Controllers\Ecommerce\Product_Types;

use Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Types_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! \Voxel\get( 'settings.product_types.enabled', true );
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 15 );
		$this->on( 'voxel/backend/product-types/screen:manage-types', '@render_manage_types_screen' );
		$this->on( 'voxel/backend/product-types/screen:create-type', '@render_create_type_screen' );
		$this->on( 'admin_post_voxel_create_product_type', '@create_product_type' );
		$this->on( 'voxel/backend/product-types/screen:edit-type', '@render_edit_screen', 30 );
		$this->on( 'admin_post_voxel_save_product_type_settings', '@save_settings' );
	}

	protected function add_menu_page() {
		add_submenu_page(
			'voxel-orders',
			__( 'Product Types', 'voxel-backend' ),
			__( 'Product Types', 'voxel-backend' ),
			'manage_options',
			'voxel-product-types',
			function() {
				$action_key = $_GET['action'] ?? 'manage-types';
				$allowed_actions = ['manage-types', 'create-type', 'edit-type'];
				$action = in_array( $action_key, $allowed_actions, true ) ? $action_key : 'manage-types';
				do_action( 'voxel/backend/product-types/screen:'.$action );
			}
		);
	}

	protected function create_product_type() {
		check_admin_referer( 'voxel_manage_product_types' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['product_type'] ) || ! is_array( $_POST['product_type'] ) ) {
			die;
		}

		$key = sanitize_key( $_POST['product_type']['key'] ?? '' );
		$label = sanitize_text_field( $_POST['product_type']['label'] ?? '' );

		$product_types = \Voxel\get( 'product_types', [] );

		if ( $key && $label && ! isset( $product_types[ $key ] ) ) {
			$product_types[ $key ] = [
				'settings' => [
					'key' => $key,
					'label' => $label,
				],
				'fields' => [],
			];
		}

		\Voxel\set( 'product_types', $product_types );

		wp_safe_redirect( admin_url( 'admin.php?page=voxel-product-types&action=edit-type&product_type='.$key ) );
		exit;
	}

	protected function render_manage_types_screen() {
		$add_type_url = admin_url('admin.php?page=voxel-product-types&action=create-type');
		$product_types = \Voxel\Product_Type::all();

		require locate_template( 'templates/backend/product-types/view-product-types.php' );
	}

	protected function render_create_type_screen() {
		require locate_template( 'templates/backend/product-types/add-product-type.php' );
	}

	protected function render_edit_screen() {
		$key = $_GET['product_type'] ?? null;
		$product_type = \Voxel\Product_Type::get( $key );
		if ( ! ( $key && $product_type ) ) {
			return;
		}

		// dump($product_type);

		// load required assets
		wp_enqueue_script('vue');
		wp_enqueue_script('sortable');
		wp_enqueue_script('vue-draggable');
		wp_enqueue_script('vx:product-type-editor.js');

		$editor_options = [
			'product_addons' => [],
			'field_types' => [],
			'product_fields' => [],
			'product_attributes' => [
				'predefined' => [
					'key' => 'attribute',
					'label' => 'Attribute',
					'description' => '',
					'choices' => [],
					'display_mode' => 'dropdown',
				],
			],
			'modules' => $this->get_modules(),
			'data_inputs' => [],
		];

		// addons config
		ob_start();
		foreach ( \Voxel\config('product_types.product_addons') as $addon_class ) {
			$addon = new $addon_class;
			$addon->set_product_type( $product_type );

			printf( '<template v-if="addon.type === \'%s\'">', $addon->get_type() );
			foreach ( $addon->get_models() as $model_key => $model ) {
				$model->set_prop( 'v-model', sprintf( 'addon[%s]', wp_json_encode( $model_key ) ) );
				echo $model->get_template();
			}
			printf( '</template>' );

			$editor_options['product_addons'][ $addon->get_type() ] = $addon->get_product_type_editor_config();
		}
		$addon_options_markup = ob_get_clean();

		// product_fields config
		ob_start();
		foreach ( \Voxel\config('product_types.product_fields') as $field_key => $field_class ) {
			$existing_props = $product_type->config('settings.product_fields')[ $field_key ] ?? [];
			$field = new $field_class( $existing_props );
			$field->set_product_type( $product_type );

			printf( '<template v-if="field.props.key === \'%s\'">', $field->get_key() );
			foreach ( $field->get_models() as $model_key => $model ) {
				$model->set_prop( 'v-model', sprintf( 'field.props[%s]', wp_json_encode( $model_key ) ) );
				echo $model->get_template();
			}
			printf( '</template>' );

			$editor_options['product_fields'][ $field->get_key() ] = (object) [
				'props' => (object) $field->get_props(),
				'conditions' => (object) $field->get_conditions(),
			];
		}
		$product_field_options_markup = ob_get_clean();

		// data inputs config
		ob_start();
		foreach ( \Voxel\config('product_types.data_inputs') as $data_input_class ) {
			$data_input = new $data_input_class;

			printf( '<template v-if="dataInput.type === \'%s\'">', $data_input->get_type() );
			foreach ( $data_input->get_controls() as $control ) {
				echo $control->get_template();
			}
			printf( '</template>' );

			$editor_options['data_inputs'][ $data_input->get_type() ] = [
				'props' => $data_input->get_props(),
			];
		}
		$data_input_options_markup = ob_get_clean();

		// preserve order of product fields
		$product_field_order = array_flip( array_keys( $product_type->config('settings.product_fields') ) );
		uasort( $editor_options['product_fields'], function( $a, $b ) use ( $product_field_order ) {
			return ( $product_field_order[ $a->props->key ] ?? 1000 ) <=> ( $product_field_order[ $b->props->key ] ?? 1000 );
		} );

		// general editor config
		printf(
			'<script type="text/javascript">window.Product_Type_Options = %s;</script>',
			wp_json_encode( (object) $editor_options )
		);

		// product type config
		printf(
			'<script type="text/javascript">window.Product_Type_Config = %s;</script>',
			wp_json_encode( (object) $product_type->repository->get_editor_config() )
		);

		require locate_template( 'templates/backend/product-types/edit-product-type.php' );
	}

	protected function save_settings() {
		check_admin_referer( 'voxel_save_product_type_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['product_type_config'] ) ) {
			die;
		}

		$config = json_decode( stripslashes( $_POST['product_type_config'] ), true );
		$settings = $config['settings'];
		$product_type = \Voxel\Product_Type::get( $settings['key'] );
		if ( ! ( $settings['key'] && $product_type && json_last_error() === JSON_ERROR_NONE ) ) {
			die;
		}

		// delete product type
		if ( ! empty( $_POST['remove_product_type'] ) && $_POST['remove_product_type'] === 'yes' ) {
			$product_type->repository->remove();

			wp_safe_redirect( admin_url( 'admin.php?page=voxel-product-types' ) );
			die;
		}

		// make sure the right modules are enabled for the selected product mode
		$product_mode = $config['settings']['product_mode'] ?? 'regular';
		$modules = $this->get_modules();
		foreach ( (array) ( $config['modules'] ) as $module_key => $module ) {
			if ( ! (
				isset( $modules[ $module_key ]['product_mode']['*'] )
				|| isset( $modules[ $module_key ]['product_mode'][ $product_mode ] )
			) ) {
				$config['modules'][ $module_key ]['enabled'] = false;
			}

			if (
				( $modules[ $module_key ]['product_mode']['*'] ?? null ) === 'required'
				|| ( $modules[ $module_key ]['product_mode'][ $product_mode ] ?? null ) === 'required'
			) {
				// edge case: base price can be disabled if addons are enabled in regular/booking modes
				if ( $module_key === 'base_price' && in_array( $product_mode, ['regular', 'booking'], true ) ) {
					if ( $config['modules']['addons']['enabled'] ) {
						continue;
					}
				}

				$config['modules'][ $module_key ]['enabled'] = true;
			}
		}

		// edit product type
		$product_type->repository->set_config( [
			'settings' => $config['settings'],
			'modules' => $config['modules'] ?? [],
		] );

		wp_safe_redirect( admin_url( sprintf(
			'admin.php?page=voxel-product-types&action=edit-type&product_type=%s%s',
			$product_type->get_key(),
			! empty( $config['tab'] ) ? '#'.$config['tab'] : ''
		) ) );
		die;
	}

	protected function get_modules(): array {
		return [
			'base_price' => [
				'module_key' => 'base_price',
				'component_key' => 'base-price-module',
				'label' => 'Price',
				'description' => 'Displays a price input when creating products of this type',
				'product_mode' => [
					'regular' => 'required',
					'variable' => 'required',
					'booking' => 'required',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/base-price.php'),
			],

			'booking' => [
				'module_key' => 'booking',
				'component_key' => 'booking-module',
				'label' => 'Booking',
				'description' => 'Display an availability calendar field to be used for bookings and appointments',
				'product_mode' => [
					'booking' => 'required',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/booking.php'),
			],

			'addons' => [
				'module_key' => 'addons',
				'component_key' => 'addons-module',
				'label' => 'Add-ons',
				'description' => 'Create a product that can be extended using add-ons',
				'product_mode' => [
					'regular' => 'optional',
					'booking' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/addons.php'),
			],

			'variations' => [
				'module_key' => 'variations',
				'component_key' => 'variations-module',
				'label' => 'Variations',
				'description' => 'Create product variations based on defined product attributes',
				'product_mode' => [
					'variable' => 'required',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/variations.php'),
			],

			'custom_prices' => [
				'module_key' => 'custom_prices',
				'component_key' => 'custom-prices-module',
				'label' => 'Custom prices',
				// 'feature_status' => 'coming_soon',
				'description' => 'Create custom product prices for specific dates, date ranges, or days of week',
				'product_mode' => [
					'regular' => 'optional',
					'booking' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/custom-prices.php'),
			],

			'stock' => [
				'module_key' => 'stock',
				'component_key' => 'stock-module',
				'label' => 'Stock',
				'description' => 'Manage stock quantity for products of this type',
				'product_mode' => [
					'regular' => 'optional',
					'variable' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/stock.php'),
			],

			'cart' => [
				'module_key' => 'cart',
				'component_key' => 'cart-module',
				'label' => 'Cart',
				'description' => 'Allow products of this type to be added to cart',
				'product_mode' => [
					'regular' => 'optional',
					'variable' => 'optional',
				],
				'settings_template' => false,
			],

			/*'multivendor' => [
				'module_key' => 'multivendor',
				'component_key' => 'multivendor-module',
				'label' => 'Multi-vendor',
				'description' => 'Product can be sold by connected vendors on your site',
				'product_mode' => [
					'regular' => 'optional',
					'variable' => 'optional',
					'booking' => 'optional',
				],
				'settings_template' => false,
				'feature_status' => 'coming_soon',
				'display_mode' => 'custom',
			],*/

			'deliverables' => [
				'module_key' => 'deliverables',
				'component_key' => 'deliverables-module',
				'label' => 'Downloads',
				'description' => 'Securely share files upon order approval',
				'product_mode' => [
					'regular' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/deliverables.php'),
			],

			'shipping' => [
				'module_key' => 'shipping',
				'component_key' => 'shipping-module',
				'label' => 'Shipping',
				'description' => 'Enable shipping for products of this type',
				'product_mode' => [
					'regular' => 'optional',
					'variable' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/shipping.php'),
			],

			'data_inputs' => [
				'module_key' => 'data_inputs',
				'component_key' => 'data-inputs-module',
				'label' => 'Data inputs',
				'description' => 'Collect additional information from customer when they buy a product of this type',
				'product_mode' => [
					'*' => 'optional',
				],
				'settings_template' => locate_template('templates/backend/product-types/modules/data-inputs.php'),
			],
		];
	}
}
