<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Deliverables_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.single_order.deliverables.upload', '@upload_deliverables' );
		$this->on( 'voxel_ajax_products.single_order.deliverables.download', '@download_deliverables' );
		$this->on( 'voxel_ajax_products.single_order.deliverables.delete', '@delete_deliverables' );
		$this->filter( 'voxel/orders/view_order/item/components', '@register_deliverables_component', 10, 3 );
	}

	protected function upload_deliverables() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			if ( ! ( $order_id && $order_item_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ) );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && $current_user->is_vendor_of( $order->get_id() ) && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! $order_item ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			$field = $order_item->get_product_field();
			$product_type = $field->get_product_type();
			if ( ! ( $product_type && $field ) ) {
				throw new \Exception( _x( 'Could not upload files.', 'orders', 'voxel' ) );
			}

			$file_field = new \Voxel\Utils\Object_Fields\File_Field( [
				'label' => 'Uploads',
				'key' => 'deliverables',
				'allowed-types' => $product_type->config( 'modules.deliverables.uploads.allowed_file_types' ),
				'max-size' => $product_type->config( 'modules.deliverables.uploads.max_size' ),
				'max-count' => $product_type->config( 'modules.deliverables.uploads.max_count' ),
				'private_upload' => true,
			] );

			$file_field_value = [];
			foreach ( (array) ( $_FILES['files']['name']['deliverables'] ?? [] ) as $filename ) {
				$file_field_value[] = 'uploaded_file';
			}

			$sanitized_files = $file_field->sanitize( $file_field_value );
			$file_field->validate( $sanitized_files );

			if ( empty( $sanitized_files ) ) {
				throw new \Exception( _x( 'Could not upload files.', 'orders', 'voxel' ) );
			}

			$file_groups = (array) $order_item->get_details( 'deliverables.manual', [] );
			$file_groups[] = [
				't' => time(),
				'ids' => $file_field->prepare_for_storage( $sanitized_files )
			];

			$order_item->set_details( 'deliverables.manual', $file_groups );

			$order_item->save();

			( new \Voxel\Events\Products\Orders\Downloads\Vendor_Shared_File_Event( $product_type ) )->dispatch( $order_item->get_id() );

			return wp_send_json( [
				'message' => _x( 'File(s) shared with customer', 'orders', 'voxel' ),
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function download_deliverables() {
		try {
			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			$file_id = absint( $_REQUEST['file_id'] ?? null );
			if ( ! ( $order_id && $order_item_id && $file_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 102 );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 105 );
			}

			if ( ! ( current_user_can('administrator') || $current_user->get_id() === $order->get_vendor_id() || $current_user->get_id() === $order->get_customer_id() ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 106 );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! $order_item ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 107 );
			}

			$field = $order_item->get_product_field();
			$product_type = $field->get_product_type();
			if ( ! ( $product_type && $field ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 109 );
			}

			$delivery_method = $_REQUEST['delivery_method'] ?? '';
			if ( $delivery_method === 'automatic' ) {
				$file_ids = explode( ',', (string) $field->get_value()['deliverables']['files'] ?? '' );
				$file_ids = array_map( 'absint', $file_ids );
				if ( ! in_array( $file_id, $file_ids, true ) ) {
					throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 110 );
				}
			} elseif ( $delivery_method === 'manual' ) {
				$file_groups = (array) $order_item->get_details( 'deliverables.manual', [] );
				$group_index = absint( $_REQUEST['group_index'] ?? 0 );
				if ( ! isset( $file_groups[ $group_index ] ) ) {
					throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 111 );
				}

				$file_ids = explode( ',', (string) $file_groups[ $group_index ]['ids'] ?? '' );
				$file_ids = array_map( 'absint', $file_ids );
				if ( ! in_array( $file_id, $file_ids, true ) ) {
					throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 112 );
				}
			} else {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			$file = get_attached_file( $file_id );
			if ( ! $file ) {
				throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 113 );
			}

			$display_filename = get_post_meta( $file_id, '_display_filename', true );
			$filename = ! empty( $display_filename ) ? $display_filename : wp_basename( $file );

			header( 'Content-type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );

			// possible values: apache, nginx, lighttpd, litespeed
			$xsendfile = apply_filters( 'voxel/order-downloads/xsendfile-header', null );
			$uri = '/'.ltrim( str_replace( [ $_SERVER['DOCUMENT_ROOT'], '\\' ], [ '', '/' ], $file ), '/' );
			if ( $xsendfile === 'apache' ) {
				header( 'X-Sendfile: '.$uri );
				exit;
			} elseif ( $xsendfile === 'nginx' ) {
				header( 'X-Accel-Redirect: '.$uri );
				exit;
			} elseif ( $xsendfile === 'lighttpd' ) {
				header( 'X-LIGHTTPD-send-file: '.$uri );
				exit;
			} elseif ( $xsendfile === 'litespeed' ) {
				header( 'X-LiteSpeed-Location: '.$uri );
				exit;
			} else {
				ob_clean();
				flush();
				readfile( $file );
				exit;
			}
		} catch ( \Exception $e ) {
			return call_user_func( apply_filters( 'wp_die_handler', '_default_wp_die_handler' ), $e->getMessage(), '', [ 'back_link' => true ] );
		}
	}

	protected function delete_deliverables() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			$file_id = absint( $_REQUEST['file_id'] ?? null );
			if ( ! ( $order_id && $order_item_id && $file_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ) );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && $current_user->is_vendor_of( $order->get_id() ) && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! $order_item ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			$group_index = absint( $_REQUEST['group_index'] ?? 0 );
			$file_groups = (array) $order_item->get_details( 'deliverables.manual', [] );
			if ( ! isset( $file_groups[ $group_index ] ) ) {
				throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 102 );
			}

			$file_ids = explode( ',', (string) $file_groups[ $group_index ]['ids'] ?? '' );
			$file_ids = array_map( 'absint', $file_ids );
			if ( ! in_array( $file_id, $file_ids, true ) ) {
				throw new \Exception( _x( 'File not found.', 'orders', 'voxel' ), 103 );
			}

			// @todo: maybe delete attachment completely (optional)
			$file_ids = array_filter( $file_ids, function( $id ) use ( $file_id ) {
				return $id !== $file_id;
			} );

			$file_groups[ $group_index ]['ids'] = join( ',', $file_ids );
			$file_groups = array_filter( $file_groups, function( $group ) {
				return ! empty( $group['ids'] );
			} );

			$order_item->set_details( 'deliverables.manual', array_values( $file_groups ) );
			$order_item->save();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function register_deliverables_component( $components, $order_item, $order ) {
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			return $components;
		}

		$field = $order_item->get_product_field();
		if ( ! $field ) {
			return $components;
		}

		$product_type = $field->get_product_type();
		if ( ! $product_type ) {
			return $components;
		}

		$details = [
			'automatic' => [],
			'manual' => [],
			'uploads' => [
				'enabled' => false,
			],
			'l10n' => [
				'files_shared' => _x( 'Files available to download', 'order downloads', 'voxel' ),
				'share_files' => _x( 'Share files with customer', 'order downloads', 'voxel' ),
				'files_available' => _x( 'Files available to download', 'order downloads', 'voxel' ),
			],
		];

		// automatic deliverables
		$deliverables = $field->get_product_field('deliverables');
		if ( $deliverables ) {
			$file_ids = explode( ',', (string) $field->get_value()['deliverables']['files'] ?? '' );
			foreach ( $file_ids as $id ) {
				if ( $url = wp_get_attachment_url( $id ) ) {
					$display_filename = get_post_meta( $id, '_display_filename', true );
					$details['automatic'][] = [
						'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $id ) ),
						'url' => add_query_arg( [
							'action' => 'products.single_order.deliverables.download',
							'order_id' => $order->get_id(),
							'order_item_id' => $order_item->get_id(),
							'file_id' => $id,
							'delivery_method' => 'automatic',
						], home_url('/?vx=1') ),
					];
				}
			}
		}

		// manual deliverables
		if ( $product_type->config('modules.deliverables.enabled') && $product_type->config('modules.deliverables.delivery_methods.manual') ) {
			if ( \Voxel\current_user()->is_vendor_of( $order->get_id() ) ) {
				$details['uploads'] = [
					'enabled' => true,
					'allowed_file_types' => $product_type->config( 'modules.deliverables.uploads.allowed_file_types' ),
					'max_size' => $product_type->config( 'modules.deliverables.uploads.max_size' ),
					'max_count' => $product_type->config( 'modules.deliverables.uploads.max_count' ),
				];
			}

			$file_groups = (array) $order_item->get_details( 'deliverables.manual', [] );

			foreach ( $file_groups as $group_index => $group ) {
				$file_ids = explode( ',', (string) $group['ids'] ?? '' );
				$files = [];
				foreach ( $file_ids as $id ) {
					if ( $url = wp_get_attachment_url( $id ) ) {
						$display_filename = get_post_meta( $id, '_display_filename', true );
						$files[] = [
							'id' => $id,
							'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $id ) ),
							'url' => add_query_arg( [
								'action' => 'products.single_order.deliverables.download',
								'order_id' => $order->get_id(),
								'order_item_id' => $order_item->get_id(),
								'group_index' => $group_index,
								'file_id' => $id,
								'delivery_method' => 'manual',
							], home_url('/?vx=1') ),
						];
					}
				}

				if ( ! empty( $files ) ) {
					$details['manual'][] = [
						'files' => $files,
					];
				}
			}
		}

		$components[] = [
			'type' => 'order-item-deliverables',
			'src' => \Voxel\get_esm_src('order-item-deliverables.js'),
			'data' => $details,
		];

		return $components;
	}
}
