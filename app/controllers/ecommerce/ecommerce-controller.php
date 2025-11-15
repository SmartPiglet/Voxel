<?php

namespace Voxel\Controllers\Ecommerce;

use Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ecommerce_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		$use_ecommerce = !! \Voxel\get( 'settings.product_types.enabled', true );

		return apply_filters( 'voxel/use_ecommerce', $use_ecommerce );
	}

	protected function dependencies() {
		new Product_Types\Product_Types_Controller;
		new Payments\Payments_Controller;
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 10 );
		$this->on( 'admin_menu', '@add_settings_page', 50 );
		$this->on( 'admin_post_voxel_save_product_types_settings', '@save_settings' );
		$this->on( 'init', '@register_catalog_post_type' );
		$this->on( 'voxel_ajax_backend.orders.delete_order', '@delete_order' );
	}

	protected function add_menu_page() {
		add_menu_page(
			__( 'Ecommerce', 'voxel-backend' ),
			__( 'Ecommerce', 'voxel-backend' ),
			'manage_options',
			'voxel-orders',
			function() {
				if ( ! empty( $_GET['order_id'] ) ) {
					$order = \Voxel\Product_Types\Orders\Order::get( $_GET['order_id'] );
					if ( ! $order ) {
						echo '<div class="wrap">'.__( 'Order not found.', 'voxel-backend' ).'</div>';
						return;
					}

					$payment_method = $order->get_payment_method();
					$customer = $order->get_customer();
					$vendor = $order->get_vendor();
					$order_items = $order->get_items();
					$child_orders = $order->get_child_orders();
					$parent_order = $order->get_parent_order();
					$vendor_fees = $order->get_vendor_fees_summary();
					$billing_interval = $order->get_billing_interval();
					$order_amount = $order->get_total();
					if ( ! is_numeric( $order_amount ) ) {
						$order_amount = $order->get_subtotal();
					}

					require locate_template( 'templates/backend/orders/edit-order.php' );
				} else {
					$table = new \Voxel\Product_Types\Order_List_Table;
					$table->prepare_items();
					require locate_template( 'templates/backend/orders/view-orders.php' );
				}
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/shopping-bag.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.300'
		);
	}

	protected function add_settings_page() {
		add_submenu_page(
			'voxel-orders',
			__( 'Settings', 'voxel-backend' ),
			__( 'Settings', 'voxel-backend' ),
			'manage_options',
			'voxel-product-types-settings',
			function() {
				$schema = \Voxel\Product_Types\Settings_Schema::get();
				foreach ( (array) \Voxel\get( 'product_settings', [] ) as $group_key => $group_values ) {
					if ( $prop = $schema->get_prop( $group_key ) ) {
						$prop->set_value( $group_values );
					}
				}

				$config = $schema->export();
				$config['tab'] = $_GET['tab'] ?? 'cart_summary';

				$props = [
					'shipping_countries' => \Voxel\Modules\Stripe_Payments\Country_Codes::shipping_supported(),
				];

				require locate_template( 'templates/backend/product-types/settings/settings.php' );
			}
		);
	}

	protected function save_settings() {
		check_admin_referer( 'voxel_save_product_types_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['config'] ) ) {
			die;
		}

		$previous_config = \Voxel\get( 'product_settings', [] );
		$submitted_config = json_decode( stripslashes( $_POST['config'] ), true );

		$schema = \Voxel\Product_Types\Settings_Schema::get();
		$schema->set_value( $previous_config );

		foreach ( $submitted_config as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$config = $schema->export();

		\Voxel\set( 'product_settings', Schema::optimize_for_storage( $config ) );

		wp_safe_redirect( add_query_arg( 'tab', $submitted_config['tab'] ?? null, admin_url( 'admin.php?page=voxel-product-types-settings' ) ) );
		die;
	}

	protected function register_catalog_post_type() {
		register_post_type( '_vx_catalog', [
			'labels' => [
				'name' => 'VX Catalog',
				'singular_name' => 'VX Product',
			],
			'public'              => false,
			'show_ui'             => false, // false
			'show_in_menu'        => false, // false
			'show_in_nav_menus'   => false,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'query_var'           => false,
			'supports' => [''],
			'delete_with_user'    => false,
			'_is_created_by_voxel' => false,
			'has_archive' => false,
			'rewrite' => [
				'slug' => 'vxcatalog',
			],
		] );

		add_filter( 'manage_edit-_vx_catalog_columns', function( $columns ) {
			$columns['vx_meta'] = 'Metadata';
			return $columns;
		} );

		add_action( 'manage__vx_catalog_posts_custom_column', function( $column, $post_id ) {
			if ( 'vx_meta' !== $column ) {
				return;
			}

			$meta = get_post_meta( $post_id );

			foreach ( $meta as $meta_key => $meta_values ) {
				if ( ! str_starts_with( $meta_key, '_vx_' ) ) {
					continue;
				}

				$meta_label = mb_substr( $meta_key, 4 );
				$meta_label = ucwords( str_replace( '_', ' ', $meta_label ) );
				$meta_value = $meta_values[0] ?? null;

				printf( '<b>%s:</b> %s<br>', $meta_label, esc_html( $meta_value ) );
			}
		}, 10, 2 );
	}

	protected function delete_order() {
		if ( ! current_user_can('manage_options') ) {
			wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
			exit;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'voxel_backend_delete_order' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
			exit;
		}

		$order = \Voxel\Product_Types\Orders\Order::get( $_REQUEST['order_id'] ?? null );
		$order->delete();

		wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
		exit;
	}

}
