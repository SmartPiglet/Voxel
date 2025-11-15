<?php

namespace Voxel\Modules\Paid_Memberships;

use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Extends \WP_List_Table to display active customers.
 *
 * @link  https://github.com/Veraxus/wp-list-table-example
 * @since 1.0
 */
class Customer_List_Table extends \WP_List_Table {

	public function get_columns() {
		return [
			'title' => _x( 'User', 'members table', 'voxel-backend' ),
			'id' => _x( 'ID', 'members table', 'voxel-backend' ),
			'plan' => _x( 'Plan', 'members table', 'voxel-backend' ),
			'amount' => _x( 'Price', 'members table', 'voxel-backend' ),
			'status' => _x( 'Status', 'members table', 'voxel-backend' ),
		];
	}

	protected function get_sortable_columns() {
		return [
			'id' => [ 'id', 'desc' ],
			'title' => [ 'username', 'asc' ],
		];
	}

	protected function column_default( $item, $column_name ) {
		$user = \Voxel\User::get( $item['id'] );
		$membership = $user->get_membership();

		if ( $column_name === 'plan' ) {
			$legacy_plan = Membership::get_legacy( $user->get_id() );
			$migrate_url = admin_url( 'admin.php?page=voxel-paid-members&migrate='.$user->get_id() );
			if ( $legacy_plan && $membership->get_type() === 'default' ) {
				if (
					$legacy_plan->get_type() === 'legacy_subscription'
					&& ! in_array( $legacy_plan->get_status(), [ 'canceled', 'incomplete_expired' ], true )
				) {
					ob_start(); ?>
					<div>Legacy plan</div>
					<a href="<?= esc_url( $migrate_url ) ?>"><b>Migrate now &rarr;</b></a>
					<?php
					return ob_get_clean();
				}
			}

			return sprintf(
				'<a href="%s">%s</a>',
				$membership->get_selected_plan()->get_edit_link(),
				$membership->get_selected_plan()->get_label()
			);
		} elseif ( $column_name === 'id' ) {
			return $user->get_id();
		} elseif ( $column_name === 'amount' ) {
			if (
				$membership->get_type() === 'order'
				&& ( $order = $membership->get_order() )
				&& ( $payment_method = $membership->get_payment_method() )
			) {
				return sprintf(
					'<a href="%s"><span class="price-amount">%s</span> %s</a>',
					esc_url( $order->get_backend_link() ),
					\Voxel\currency_format( $membership->get_amount(), $membership->get_currency(), false ),
					\Voxel\interval_format( $membership->get_interval(), $membership->get_frequency() )
				);
			} else {
				return '';
			}
		} elseif ( $column_name === 'status' ) {
			if (
				$membership->get_type() === 'order'
				&& ( $order = $membership->get_order() )
				&& ( $payment_method = $membership->get_payment_method() )
			) {
				return sprintf(
					'<div class="order-status order-status-%s %s">%s</div>',
					esc_attr( $order->get_status() ),
					esc_attr( \Voxel\Order::get_status_config()[ $order->get_status() ]['class'] ?? '' ),
					$order->get_status_label()
				);
			} else {
				return '';
			}
		}
	}

	protected function column_title( $item ) {
		$user = \Voxel\User::get( $item['id'] );
		ob_start(); ?>
			<?= $user->get_avatar_markup(40) ?>
			<div class="item-title">
				<a href="<?= esc_url( $user->get_edit_link() ) ?>">
					<b><?= esc_html( $user->get_display_name() ) ?></b>
				</a>
				<div class="row-actions">
					<span>
						<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-paid-members&customer='.$user->get_id() ) ) ?>">Details</a>
					</span>
				</div>
			</div>
		<?php return ob_get_clean();
	}

	protected function get_views() {
		global $wpdb;

		$meta_key = \Voxel\get_site_specific_user_meta_key(
			\Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan'
		);

		$counts = $wpdb->get_results( <<<SQL
			SELECT
				( CASE
					WHEN (
						JSON_VALID( m.meta_value )
						AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.type' ) ) = 'order'
					)
					THEN 'subscriber'
					ELSE 'free'
				END ) AS membership_type,
				COUNT(*) AS total
			FROM {$wpdb->users} AS u
			{$this->_join_blog_sql()}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			GROUP BY membership_type
		SQL, OBJECT_K );

		$total_count = 0;
		foreach ( $counts as $count ) {
			$total_count += absint( $count->total );
		}

		$current_type = $_GET['type'] ?? null;
		$views['all'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-paid-members'),
			( empty( $current_type ) || $current_type === 'all' ) ? 'current' : '',
			_x( 'All', 'members table', 'voxel-backend' ),
			sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $total_count ) ),
		);

		$views['subscriber'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-paid-members&type=subscriber'),
			( $current_type === 'subscriber' ) ? 'current' : '',
			_x( 'Subscribers', 'members table', 'voxel-backend' ),
			sprintf( ' <span class="count">(%s)</span>', number_format_i18n( absint( $counts['subscriber']->total ?? 0 ) ) ),
		);

		$views['free'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-paid-members&type=free'),
			( $current_type === 'free' ) ? 'current' : '',
			_x( 'Free', 'members table', 'voxel-backend' ),
			sprintf( ' <span class="count">(%s)</span>', number_format_i18n( absint( $counts['free']->total ?? 0 ) ) ),
		);

		return $views;
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

		$order = ( $_GET['order'] ?? null ) === 'asc' ? 'ASC' : 'DESC';
		$custom_orderby = $_GET['orderby'] ?? null;
		if ( $custom_orderby === 'username' ) {
			$orderby = 'u.display_name';
		} else {
			$orderby = 'u.ID';
		}

		$search = '';
		if ( ! empty( $_GET['s'] ) ) {
			$search_string = esc_sql( $_GET['s'] );
			$search_like = '%'.$wpdb->esc_like( $search_string ).'%';
			$search = $wpdb->prepare(
				"AND ( u.user_login = %s OR u.user_email = %s OR u.ID = %s OR u.display_name LIKE %s )",
				$search_string, $search_string, $search_string, $search_like
			);
		}

		$where_plan = '';
		if ( ! empty( $_GET['plan'] ) ) {
			$plan = esc_sql( $_GET['plan'] );
			if ( $plan === 'default' ) {
				$where_plan = "AND (
					(
						JSON_VALID( m.meta_value )
						AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) = '{$plan}'
					) OR m.meta_key IS NULL
				)";
			} else {
				$where_plan = "AND (
					JSON_VALID( m.meta_value )
					AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) = '{$plan}'
				)";
			}
		}

		$current_type = $_GET['type'] ?? null;
		if ( $current_type === 'subscriber' ) {
			$where_type = "AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.type' ) ) = 'order'";
		} elseif ( $current_type === 'free' ) {
			$where_type = "AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(m.meta_value, '$.type')), '') <> 'order'";
		} else {
			$where_type = '';
		}

		$join_orders = '';
		$where_status = '';
		if ( ! empty( $_GET['status'] ) && $current_type === 'subscriber' ) {

			$join_orders = "INNER JOIN {$wpdb->prefix}vx_orders AS orders ON (
				JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.type' ) ) = 'order'
				AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.order_id' ) ) = orders.id
			)";

			$where_status = $wpdb->prepare( "AND orders.status = %s", (string) wp_unslash( $_GET['status'] ) );
		}

		$meta_key = \Voxel\get_site_specific_user_meta_key(
			\Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan'
		);

		$sql = <<<SQL
			SELECT u.ID AS id
			FROM {$wpdb->users} as u
			{$this->_join_blog_sql()}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			{$join_orders}
			WHERE 1=1
				{$where_type}
				{$where_plan}
				{$where_status}
				{$search}
			ORDER BY {$orderby} {$order}
			LIMIT {$limit} OFFSET {$offset}
		SQL;

		// dd_sql($sql);

		$results = $wpdb->get_results( $sql, ARRAY_A );

		cache_users( array_column( $results, 'id' ) );

		$count = absint( $wpdb->get_var( <<<SQL
			SELECT COUNT(*)
			FROM {$wpdb->users} as u
			{$this->_join_blog_sql()}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			WHERE 1=1
				{$where_type}
				{$where_plan}
				{$search}
		SQL ) );

		$this->items = $results;
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
		?>
		<input type="hidden" name="type" value="<?= esc_attr( $selected_type ) ?>">
		<input type="search" name="s" class="ts-search-input"
			value="<?= esc_attr( wp_unslash( $_GET['s'] ?? '' ) ) ?>"
			placeholder="Search by name, email, or user id">
		<select name="plan">
			<option value="">All plans</option>
			<?php foreach ( \Voxel\Plan::all() as $plan ): ?>
				<option value="<?= esc_attr( $plan->get_key() ) ?>" <?= selected( $selected_plan === $plan->get_key() ) ?>>
					<?= esc_html( $plan->get_label() ) ?>
				</option>
			<?php endforeach ?>
		</select>
		<?php if ( $selected_type === 'subscriber' ):
			$meta_key = \Voxel\get_site_specific_user_meta_key(
				\Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan'
			);

			$status_config = \Voxel\Order::get_status_config();
			$statuses = $wpdb->get_results( <<<SQL
				SELECT
					orders.status,
					COUNT(*) AS total
				FROM {$wpdb->users} AS u
				{$this->_join_blog_sql()}
				LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
				INNER JOIN {$wpdb->prefix}vx_orders AS orders ON (
					JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.type' ) ) = 'order'
					AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.order_id' ) ) = orders.id
				)
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
		<?php endif ?>
		<input type="submit" class="button" value="Filter">
		<?php
	}

	protected function _join_blog_sql(): string {
		global $wpdb;

		$join = '';
		if ( get_current_blog_id() && is_multisite() ) {
			$blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
			$join = "INNER JOIN {$wpdb->usermeta} AS b ON (
				u.ID = b.user_id AND b.meta_key = '{$blog_prefix}capabilities'
			)";
		}

		return $join;
	}
}
