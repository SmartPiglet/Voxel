<?php

namespace Voxel\Product_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_List_Table extends \WP_List_Table {

	public function get_columns() {
		$columns = [
			// 'cb' => '<input type="checkbox">',
			'id' => _x( 'ID', 'orders table', 'voxel-backend' ),
			'customer' => _x( 'Customer', 'orders table', 'voxel-backend' ),
			'amount' => _x( 'Amount', 'orders table', 'voxel-backend' ),
			'status' => _x( 'Status', 'orders table', 'voxel-backend' ),
			'vendor' => _x( 'Vendor', 'orders table', 'voxel-backend' ),
			'created_at' => _x( 'Date', 'orders table', 'voxel-backend' ),
			// 'payment_method' => _x( 'Type', 'orders table', 'voxel-backend' ),
			// 'details' => '',
		];

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = [
			'id' => [ 'created_at', 'asc' ],
			'created_at' => [ 'created_at', 'desc' ],
		];

		return $sortable_columns;
	}

	protected function get_primary_column_name() {
		return 'customer';
	}

	protected function column_default( $order, $column_name ) {
		$customer = $order->get_customer();
		$vendor = $order->get_vendor();

		if ( $column_name === 'id' ) {
			if ( $parent_order = $order->get_parent_order() ) {
				return sprintf( '<a href="%s"><b>Suborder #%d</b></a><br><a href="%s">Order: #%d</a>', esc_url( $order->get_backend_link() ), $order->get_id(), esc_url( $parent_order->get_backend_link() ), $parent_order->get_id() );
			} else {
				return sprintf( '<a href="%s"><b>Order #%d</b></a>', esc_url( $order->get_backend_link() ), $order->get_id() );
			}
		} elseif ( $column_name === 'amount' ) {
			if ( $order->get_total() !== null ) {
				if (
					( $payment_method = $order->get_payment_method() )
					&& $payment_method->is_subscription()
					&& ( $interval = $payment_method->get_billing_interval() )
				) {
					return sprintf(
						'<span class="price-amount">%s</span> %s',
						\Voxel\currency_format( $order->get_total(), $order->get_currency(), false ),
						\Voxel\interval_format( $interval['interval'], $interval['interval_count'] )
					);
				}

				return sprintf(
					'<span class="price-amount">%s</span>',
					\Voxel\currency_format( $order->get_total(), $order->get_currency(), false )
				);
			}

			if ( $order->get_subtotal() !== null ) {
				return sprintf(
					'<span class="price-amount price-subtotal">%s</span>',
					\Voxel\currency_format( $order->get_subtotal(), $order->get_currency(), false )
				);
			}

			return '&mdash;';
		} elseif ( $column_name === 'customer' ) {
			if ( $customer ) {
				ob_start(); ?>
				<div class="item-details">
					<div class="item-image"><?= $customer->get_avatar_markup(24) ?></div>
					<div class="item-title">
						<span>
							<a href="<?= esc_url( $customer->get_edit_link() ) ?>">
								<b><?= esc_html( $customer->get_display_name() ) ?></b>
							</a>
						</span>
					</div>
				</div>
				<?php return ob_get_clean();
			} else {
				return '(deleted)';
			}
		} elseif ( $column_name === 'vendor' ) {
			if ( $order->has_vendor() && $vendor ) {
				ob_start(); ?>
				<div class="item-details">
					<div class="item-image"><?= $vendor->get_avatar_markup(24) ?></div>
					<div class="item-title">
						<span>
							<a href="<?= esc_url( $vendor->get_edit_link() ) ?>">
								<?= esc_html( $vendor->get_display_name() ) ?>
							</a>
						</span>
					</div>
				</div>
				<?php return ob_get_clean();
			} else {
				ob_start(); ?>
				<div class="item-details">
					<div class="item-title">
						<span><?= _x( 'Platform', 'orders table', 'voxel-backend' ) ?></span>
					</div>
				</div>
				<?php return ob_get_clean();
			}
		} elseif ( $column_name === 'status' ) {
			$config = \Voxel\Order::get_status_config();
			return sprintf(
				'<div class="order-status order-status-%s %s">%s</div>',
				esc_attr( $order->get_status() ),
				esc_attr( $config[ $order->get_status() ]['class'] ?? '' ),
				$order->get_status_label()
			);
		} elseif ( $column_name === 'created_at' ) {
			if ( $created_at = $order->get_created_at() ) {
				return \Voxel\datetime_format( $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			}

			return '&mdash;';
		} elseif ( $column_name === 'details' ) {
			return sprintf(
				'<a href="%s" class="button right">%s</a>',
				esc_url( admin_url( 'admin.php?page=voxel-orders&order_id='.$order->get_id() ) ),
				_x( 'Details', 'orders table', 'voxel-backend' )
			);
		} elseif ( $column_name === 'payment_method' ) {
			if ( $payment_method = $order->get_payment_method() ) {
				return $payment_method->get_label();
			}

			return '&mdash;';
		}

		return null;
	}

	protected function get_views() {
		global $wpdb;

		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
		$total_counts = $wpdb->get_results( <<<SQL
			SELECT status, COUNT(*) AS total
			FROM {$wpdb->prefix}vx_orders
			WHERE testmode IS {$testmode}
			GROUP BY status
		SQL );

		$counts = [];
		$total_count = 0;

		foreach ( $total_counts as $count ) {
			$counts[ $count->status ] = absint( $count->total );
			$total_count += absint( $count->total );
		}

		$active = $_GET['status'] ?? null;

		$views['all'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-orders'),
			empty( $active ) ? 'current' : '',
			_x( 'All', 'orders table', 'voxel-backend' ),
			$total_count > 0 ? sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $total_count ) ) : '',
		);

		foreach ( \Voxel\Product_Types\Orders\Order::get_status_config() as $status_key => $status ) {
			if ( isset( $counts[ $status_key ] ) ) {
				$views[ $status_key ] = sprintf(
					'<a href="%s" class="%s">%s%s</a>',
					admin_url( 'admin.php?page=voxel-orders&status='.$status_key ),
					$active === $status_key ? 'current' : '',
					$status['label'],
					sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $counts[ $status_key ] ) ),
				);
			}
		}

		return $views;
	}

	protected function extra_tablenav( $which ) {
		if ( $which !== 'top' ) {
			return;
		}

		global $wpdb;

		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';

		$selected_product_type = wp_unslash( $_GET['product_type'] ?? '' );
		$product_types = [];
		$available_product_types = $wpdb->get_results( <<<SQL
			SELECT items.product_type AS product_type, COUNT(DISTINCT orders.id) AS order_count
			FROM {$wpdb->prefix}vx_orders AS orders
			LEFT JOIN {$wpdb->prefix}vx_order_items AS items ON orders.id = items.order_id
			WHERE items.product_type IS NOT NULL
				AND orders.testmode IS {$testmode}
			GROUP BY items.product_type
		SQL, OBJECT_K );

		foreach ( $available_product_types as $item ) {
			if ( $product_type = \Voxel\Product_Type::get( $item->product_type ) ) {
				$product_types[ $product_type->get_key() ] = sprintf(
					'%s (%d)',
					$product_type->get_label(),
					absint( $item->order_count )
				);
			}
		}
		?>
		<input type="text" name="search_orders" placeholder="<?= esc_attr( _x( 'Search orders', 'orders table', 'voxel-backend' ) ) ?>" value="<?= esc_attr( wp_unslash( $_GET['search_orders'] ?? '' ) ) ?>">
		<input type="text" name="search_customer" placeholder="<?= esc_attr( _x( 'Search customer', 'orders table', 'voxel-backend' ) ) ?>" value="<?= esc_attr( wp_unslash( $_GET['search_customer'] ?? '' ) ) ?>">
		<input type="text" name="search_vendor" placeholder="<?= esc_attr( _x( 'Search vendor', 'orders table', 'voxel-backend' ) ) ?>" value="<?= esc_attr( wp_unslash( $_GET['search_vendor'] ?? '' ) ) ?>">
		<select name="product_type">
			<option value="">All product types</option>
			<?php foreach ( $product_types as $key => $label ): ?>
				<option value="<?= esc_attr( $key ) ?>" <?= selected( $selected_product_type === $key ) ?>>
					<?= esc_html( $label ) ?>
				</option>
			<?php endforeach ?>
		</select>
		<input type="submit" class="button" value="Filter">
		<?php
	}

	public function prepare_items() {
		global $wpdb;

		$page = $this->get_pagenum();
		$limit = 15;
		$offset = $limit * ( $page - 1 );
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$args = [
			'limit' => $limit,
			'offset' => $offset,
			'order' => ( $_GET['order'] ?? null ) === 'asc' ? 'asc' : 'desc',
			'status' => ! empty( $_GET['status'] ?? null ) ? $_GET['status'] : null,
			'with_items' => false,
			// 'parent_id' => 0,
			// 'with_child_orders' => true,

			// @todo
			'search' => ! empty( $_GET['search_orders'] ?? null ) ? wp_unslash( $_GET['search_orders'] ) : null,
			'search_customer' => ! empty( $_GET['search_customer'] ?? null ) ? wp_unslash( $_GET['search_customer'] ) : null,
			'search_vendor' => ! empty( $_GET['search_vendor'] ?? null ) ? wp_unslash( $_GET['search_vendor'] ) : null,
			'product_type' => ! empty( $_GET['product_type'] ?? null ) ? wp_unslash( $_GET['product_type'] ) : null,
		];

		/*if ( $args['search'] !== null || $args['search_customer'] !== null || $args['search_vendor'] !== null || $args['product_type'] !== null ) {
			$args['parent_id'] = null;
			$args['with_child_orders'] = false;
		}*/

		$results = \Voxel\Product_Types\Orders\Order::query( $args );
		$count = \Voxel\Product_Types\Orders\Order::count( $args );

		$items = $results;
		/*foreach ( $results as $order ) {
			$items[] = $order;
			foreach ( $order->get_child_orders() as $child_order ) {
				$items[] = $child_order;
			}
		}*/

		$this->items = $items;
		$this->set_pagination_args( [
			'total_items' => $count,
			'per_page' => $limit,
			'total_pages' => absint( ceil( $count / $limit ) ),
		] );
	}
}
