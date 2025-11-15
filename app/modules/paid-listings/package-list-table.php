<?php

namespace Voxel\Modules\Paid_Listings;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Package_List_Table extends \WP_List_Table {

	public function get_columns() {
		return [
			'title' => _x( 'User', 'packages table', 'voxel-backend' ),
			'plan' => _x( 'Plan', 'packages table', 'voxel-backend' ),
			'order' => _x( 'Order', 'packages table', 'voxel-backend' ),
			'status' => _x( 'Status', 'packages table', 'voxel-backend' ),
			'date' => _x( 'Date', 'packages table', 'voxel-backend' ),
			'usage' => _x( 'Usage', 'packages table', 'voxel-backend' ),
		];
	}

	protected function column_default( $package, $column_name ) {
		$plan = $package->get_plan();
		$order_item = $package->order_item;
		$order = $package->order;
		$payment_method = $order->get_payment_method();

		if ( $column_name === 'plan' ) {
			return $plan ? sprintf(
				'<a href="%s">%s</a>',
				$plan->get_edit_link(),
				$plan->get_label()
			) : '';
		} elseif ( $column_name === 'order' ) {
			ob_start(); ?>
			<a href="<?= esc_url( $order->get_backend_link() ) ?>">
				Order #<?= $order->get_id() ?>
			</a>
			<?php if ( $order->get_total() !== null ): ?>
				<span class="price-amount">
					<?= \Voxel\currency_format( $order->get_total(), $order->get_currency(), false ) ?>
					<?php if ( $payment_method && $payment_method->is_subscription() && ( $interval = $payment_method->get_billing_interval() ) ): ?>
						<?= \Voxel\interval_format( $interval['interval'], $interval['interval_count'] ) ?>
					<?php endif ?>
				</span>
			<?php endif ?>
			<?php return ob_get_clean();
		} elseif ( $column_name === 'status' ) {
			$config = \Voxel\Order::get_status_config();
			return sprintf(
				'<div class="order-status order-status-%s %s">%s</div>',
				esc_attr( $order->get_status() ),
				esc_attr( $config[ $order->get_status() ]['class'] ?? '' ),
				$order->get_status_label()
			);
		} elseif ( $column_name === 'date' ) {
			if ( $created_at = $order->get_created_at() ) {
				return \Voxel\datetime_format( $created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			}

			return '&mdash;';
		} elseif ( $column_name === 'usage' ) {
			$total = 0;
			$used = 0;

			foreach ( $package->get_limits() as $limit ) {
				$total += $limit['total'];
				$used += $limit['usage']['count'];
			}

			$used = min( $total, $used );

			return sprintf(
				'<a href="%s"><span class="package-usage">%d/%d used</span></a>',
				esc_url( admin_url( 'admin.php?page=voxel-paid-listings&package='.$package->get_id().'#package-usage' ) ),
				$used,
				$total
			);
		}
	}

	protected function column_title( $package ) {
		$customer = $package->order->get_customer();
		ob_start(); ?>
			<div class="item-user">
				<?= $customer->get_avatar_markup(40) ?>
				<div class="item-title">
					<a href="<?= esc_url( $customer->get_edit_link() ) ?>">
						<b><?= esc_html( $customer->get_display_name() ) ?></b>
					</a>
					<div class="row-actions">
						<span>
							<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-paid-listings&package='.$package->get_id() ) ) ?>">Edit plan</a>
						</span>
					</div>
				</div>
			</div>
		<?php return ob_get_clean();
	}

	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];

		$page = $this->get_pagenum();
		$limit = 10;
		$offset = $limit * ( $page - 1 );

		$where_status = '';
		if ( ! empty( $_GET['status'] ) ) {
			$where_status = $wpdb->prepare( "AND orders.status = %s", (string) wp_unslash( $_GET['status'] ) );
		}

		$search_customer = '';
		if ( ! empty( $_GET['s'] ) ) {
			$search_string = esc_sql( $_GET['s'] );
			$search_like = '%'.$wpdb->esc_like( $search_string ).'%';
			$search_customer = $wpdb->prepare(
				"AND ( users.user_login = %s OR users.user_email = %s OR users.ID = %s OR users.display_name LIKE %s )",
				$search_string, $search_string, $search_string, $search_like
			);
		}

		$search_order = '';
		if ( ! empty( $_GET['order_id'] ) ) {
			$order_id = absint( $_GET['order_id'] );
			if ( $order_id >= 1 ) {
				$search_order = $wpdb->prepare( "AND orders.id = %d", $order_id );
			}
		}

		$where_plan = '';
		if ( ! empty( $_GET['plan'] ) ) {
			$plan = esc_sql( $_GET['plan'] );
			$where_plan = "AND (
				JSON_VALID( items.details )
				AND JSON_UNQUOTE( JSON_EXTRACT( items.details, '$.\"voxel:listing_plan\".plan' ) ) = '{$plan}'
			)";
		}

		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
		$sql = <<<SQL
			SELECT items.id AS id FROM {$wpdb->prefix}vx_order_items AS items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( items.order_id = orders.id )
			LEFT JOIN {$wpdb->users} AS users ON (orders.customer_id = users.ID)
			WHERE items.field_key = 'voxel:listing_plan'
				{$where_status}
				{$search_customer}
				{$search_order}
				{$where_plan}
				AND orders.testmode IS {$testmode}
			ORDER BY orders.id DESC
			LIMIT {$limit} OFFSET {$offset}
		SQL;

		$order_item_ids = $wpdb->get_col($sql);

		$order_items = \Voxel\Order_Item::query( [
			'id' => ! empty( $order_item_ids ) ? $order_item_ids : [0],
			'limit' => null,
		] );

		$packages = [];
		foreach ( $order_items as $order_item ) {
			if ( $package = Module\Listing_Package::get( $order_item ) ) {
				$packages[] = $package;
			}
		}

		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
		$count = absint( $wpdb->get_var( <<<SQL
			SELECT COUNT(DISTINCT items.id) FROM {$wpdb->prefix}vx_order_items AS items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( items.order_id = orders.id )
			LEFT JOIN {$wpdb->users} AS users ON (orders.customer_id = users.ID)
			WHERE items.field_key = 'voxel:listing_plan'
				{$where_status}
				{$search_customer}
				{$search_order}
				{$where_plan}
				AND orders.testmode IS {$testmode}
		SQL ) );

		$this->items = $packages;
		$this->set_pagination_args( [
			'total_items' => $count,
			'per_page' => $limit,
			'total_pages' => absint( ceil( $count / $limit ) ),
		] );
	}

	protected function extra_tablenav( $which ) {
		global $wpdb;
		if ( $which !== 'top' ) {
			return;
		}

		$selected_type = wp_unslash( $_GET['type'] ?? '' );
		$selected_plan = wp_unslash( $_GET['plan'] ?? '' );
		$selected_status = wp_unslash( $_GET['status'] ?? '' );
		$selected_order_id = wp_unslash( $_GET['order_id'] ?? '' );
		?>
		<select name="plan">
			<option value="">All plans</option>
			<?php foreach ( Module\Listing_Plan::all() as $plan ): ?>
				<option value="<?= esc_attr( $plan->get_key() ) ?>" <?= selected( $selected_plan === $plan->get_key() ) ?>>
					<?= esc_html( $plan->get_label() ) ?>
				</option>
			<?php endforeach ?>
		</select>
		<?php
		$status_config = \Voxel\Order::get_status_config();
		$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
		$statuses = $wpdb->get_results( <<<SQL
			SELECT orders.status, COUNT(*) AS total
			FROM {$wpdb->prefix}vx_order_items AS items
			LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON ( items.order_id = orders.id )
			WHERE items.field_key = 'voxel:listing_plan' AND orders.testmode IS {$testmode}
			GROUP BY orders.status
		SQL, OBJECT_K );
		?>
		<select name="status">
			<option value="">All statuses</option>
			<?php foreach ( $statuses as $status_key => $status ): ?>
				<?php if ( isset( $status_config[ $status_key ] ) ): ?>
					<option value="<?= esc_attr( $status_key ) ?>" <?= selected( $selected_status === $status_key ) ?>>
						<?= esc_html( $status_config[ $status_key ]['label'] ) ?>
						(<?= absint( $status->total ) ?>)
					</option>
				<?php endif ?>
			<?php endforeach ?>
		</select>
		<input type="search" name="s" class="ts-search-input"
			value="<?= esc_attr( wp_unslash( $_GET['s'] ?? '' ) ) ?>"
			placeholder="Customer name, email, or ID">
		<input type="number" name="order_id" class="ts-search-order"
			value="<?= esc_attr( wp_unslash( $_GET['order_id'] ?? '' ) ) ?>"
			placeholder="Order ID">
		<input type="submit" class="button" value="Filter">
		<?php
	}

}
