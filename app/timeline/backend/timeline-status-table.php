<?php

namespace Voxel\Timeline\Backend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Timeline_Status_Table extends \WP_List_Table {

	public function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox">',
			'title' => 'Author',
			'content' => 'Content',
			'date' => 'Submitted on',
		];

		return $columns;
	}

	protected function get_sortable_columns() {
		return [
			'date' => [ 'date', 'desc' ],
		];
	}

	protected function column_title( $status ) {
		$post = $status->get_post();
		$publisher = $status->get_publisher();
		if ( $publisher === null ) {
			return '&mdash;';
		}

		$context = '';
		$feed = $status->get_feed();
		if ( $feed === 'post_reviews' ) {
			if ( $post ) {
				$context = sprintf(
					'Reviewed <a href="%s">%s</a>',
					esc_url( get_edit_post_link( $post->get_id() ) ),
					esc_html( $post->get_display_name() )
				);
			}
		} elseif ( $feed === 'post_wall' ) {
			if ( $post ) {
				$context = sprintf(
					'Posted on <a href="%s">%s</a>',
					esc_url( get_edit_post_link( $post->get_id() ) ),
					esc_html( $post->get_display_name() )
				);
			}
		} elseif ( $feed === 'post_timeline' ) {
			$context = 'Posted on their timeline';
		} elseif ( $feed === 'user_timeline' ) {
			if ( $repost_of = $status->get_repost_of() ) {
				$context = sprintf(
					'Reposted <a href="%s">#%d</a>',
					esc_url( admin_url( 'admin.php?page=voxel-timeline&search_id='.$repost_of->get_id() ) ),
					$status->get_id(),
				);
			} elseif ( $quote_of = $status->get_quote_of() ) {
				$context = sprintf(
					'Quoted <a href="%s">#%d</a>',
					esc_url( admin_url( 'admin.php?page=voxel-timeline&search_id='.$quote_of->get_id() ) ),
					$status->get_id(),
				);
			} else {
				$context = 'Posted on their timeline';
			}
		}

		ob_start(); ?>
			<?= $publisher->get_avatar_markup(24) ?>
			<div class="item-title">
				<a href="<?= esc_url( $publisher->get_edit_link() ) ?>">
					<b><?= esc_html( $publisher->get_display_name() ) ?></b>
				</a>
				<?php if ( $status->get_moderation_status() === \Voxel\MODERATION_PENDING ): ?>
					<b class="post-state">&mdash; Pending</b>
				<?php endif ?>
				<div class="item-subtitle">
					<?= $context ?>
				</div>
				<div class="row-actions" data-status-id="<?= esc_attr( $status->get_id() ) ?>">
					<?php if ( $status->get_moderation_status() === \Voxel\MODERATION_PENDING ): ?>
						<span>
							<a href="<?= esc_url( $status->get_link() ) ?>">View</a> |
						</span>
						<span>
							<a class="tl__action" data-action="approve" href="#" style="color: #299808;">Approve</a> |
						</span>
						<span>
							<a class="tl__action" data-action="decline" href="#" style="color: #b32d2e;">Decline</a> |
						</span>
					<?php else: ?>
						<span>
							<a href="<?= esc_url( $status->get_link() ) ?>">View</a> |
						</span>
						<span>
							<a class="tl__action" data-action="unapprove" href="#" style="color: #d88211;">Unapprove</a> |
						</span>
						<span>
							<a class="tl__action" data-action="delete" href="#" style="color: #b32d2e;">Delete</a> |
						</span>
					<?php endif ?>

					<span>
						ID: <?= $status->get_id() ?>
					</span>
				</div>
			</div>
		<?php return ob_get_clean();
	}

	protected function column_default( $status, $column_name ) {
		if ( $column_name === 'id' ) {
			return $status->get_id();
		} elseif ( $column_name === 'content' ) {
			$content = $status->get_content_for_display();
			$files = $status->get_files();
			ob_start(); ?>

			<?php if ( ! empty( $content ) ): ?>
				<div class="ts__status-content"><?= $content ?></div>
			<?php endif ?>

			<?php if ( $status->get_feed() === 'post_reviews' && ( $review_score = $status->get_review_score_for_display() ) ): ?>
				<div class="ts__status-review-score">
					<label><b>Review score</b></label>
					<div><?= esc_html( $review_score ) ?></div>
				</div>
			<?php endif ?>

			<?php if ( ! empty( $files ) ): ?>
				<div class="ts__status-files">
					<label><b>Media</b></label>
					<?php foreach ( $files as $file ): ?>
						<a href="<?= esc_url( $file['url'] ) ?>" target="_blank"><?= esc_html( $file['name'] ) ?></a>
					<?php endforeach ?>
				</div>
			<?php endif ?>

			<?php return ob_get_clean();
		} elseif ( $column_name === 'date' ) {
			$created_at = strtotime( $status->get_created_at() );
			ob_start(); ?>
			<?= sprintf( '%s %s', date_i18n( 'Y/m/d', $created_at ), \Voxel\time_format( $created_at ) ) ?>
			<?php return ob_get_clean();
		}
	}

	protected function column_cb( $status ) {
		return sprintf( '<input type="checkbox" name="items[]" value="%d">', $status->get_id() );
	}

	protected function get_views() {
		global $wpdb;

		$map = [
			0 => 'pending',
			1 => 'approved',
		];

		$total_counts = $wpdb->get_results( <<<SQL
			SELECT moderation, COUNT(*) AS total
			FROM {$wpdb->prefix}voxel_timeline
			GROUP BY moderation
		SQL );

		$counts = [];
		$total_count = 0;

		foreach ( $total_counts as $count ) {
			if ( isset( $map[ $count->moderation ] ) ) {
				$counts[ $map[ $count->moderation ] ] = absint( $count->total );
			}

			$total_count += absint( $count->total );
		}

		$active = $_GET['status'] ?? null;

		$views['all'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-timeline'),
			empty( $active ) ? 'current' : '',
			'All',
			$total_count > 0 ? sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $total_count ) ) : '',
		);

		$views['pending'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url( 'admin.php?page=voxel-timeline&status=pending' ),
			$active === 'pending' ? 'current' : '',
			'Pending approval',
			sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $counts['pending'] ?? 0 ) ),
		);

		$views['approved'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url( 'admin.php?page=voxel-timeline&status=approved' ),
			$active === 'approved' ? 'current' : '',
			'Approved',
			sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $counts['approved'] ?? 0 ) ),
		);

		return $views;
	}

	protected function extra_tablenav( $which ) {
		if ( $which !== 'top' ) {
			return;
		}
		$active_feed = wp_unslash( $_GET['search_feed'] ?? '' );
		?>
		<input type="number" name="search_id" placeholder="ID" value="<?= esc_attr( wp_unslash( $_GET['search_id'] ?? '' ) ) ?>">
		<input type="number" name="search_user_id" placeholder="User ID" value="<?= esc_attr( wp_unslash( $_GET['search_user_id'] ?? '' ) ) ?>">
		<input type="number" name="search_post_id" placeholder="Post ID" value="<?= esc_attr( wp_unslash( $_GET['search_post_id'] ?? '' ) ) ?>">
		<select name="search_feed">
			<option value="">All feeds</option>
			<option value="post_timeline" <?= selected( $active_feed === 'post_timeline' ) ?>>Post timeline</option>
			<option value="post_reviews" <?= selected( $active_feed === 'post_reviews' ) ?>>Post reviews</option>
			<option value="post_wall" <?= selected( $active_feed === 'post_wall' ) ?>>Post wall</option>
			<option value="user_timeline" <?= selected( $active_feed === 'user_timeline' ) ?>>User timeline</option>
		</select>
		<input type="submit" class="button" value="Filter">
		<button type="button" id="tl__reindex" class="button">Reindex</button>
		<?php
	}

	public function get_bulk_actions() {
		return [
			'approve' => _x( 'Mark as approved', 'timeline', 'voxel-backend' ),
			'pending' => _x( 'Mark as pending', 'timeline', 'voxel-backend' ),
			'delete'  => _x( 'Delete', 'timeline', 'voxel-backend' ),
		];
	}

	public function process_bulk_action() {
		$action = $this->current_action();
		if ( empty( $action ) ) {
			return;
		}

		check_admin_referer('bulk-' . $this->_args['plural']);
		if ( empty( $_GET['items'] ) || ! is_array( $_GET['items'] ) ) {
			return;
		}

		// Sanitize/validate IDs
		$item_ids = array_map( 'absint', $_GET['items'] );
		$item_ids = array_filter( $item_ids );

		if ( empty( $item_ids ) ) {
			return;
		}

		if ( $action === 'approve' ) {
			foreach ( $item_ids as $status_id ) {
				if ( $status = \Voxel\Timeline\Status::get( $status_id ) ) {
					$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
				}
			}
		} elseif ( $action === 'pending' ) {
			foreach ( $item_ids as $status_id ) {
				if ( $status = \Voxel\Timeline\Status::get( $status_id ) ) {
					$status->update( 'moderation', \Voxel\MODERATION_PENDING );
				}
			}
		} elseif ( $action === 'delete' ) {
			foreach ( $item_ids as $status_id ) {
				if ( $status = \Voxel\Timeline\Status::get( $status_id ) ) {
					$status->delete();
				}
			}
		}

		?>
		<script type="text/javascript">
			jQuery( () => {
				Voxel_Backend.deleteSearchParam('action');
				Voxel_Backend.deleteSearchParam('items');
				location.reload();
			} );
		</script>
		<?php
	}

	public function prepare_items() {
		// handle any bulk actions first
		$this->process_bulk_action();

		global $wpdb;

		$page = $this->get_pagenum();
		$limit = 25;
		$offset = $limit * ( $page - 1 );
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$moderation = null;
		if ( ! empty( $_GET['status'] ) ) {
			if ( $_GET['status'] === 'pending' ) {
				$moderation = 0;
			} elseif ( $_GET['status'] === 'approved' ) {
				$moderation = 1;
			}
		}

		$args = [
			'order_by' => 'created_at',
			'order' => ( $_GET['order'] ?? null ) === 'asc' ? 'asc' : 'desc',
			'offset' => $offset,
			'limit' => $limit,
			'moderation' => $moderation,
			'moderation_strict' => true,
			'_get_total_count' => true,
		];

		$search_feed = wp_unslash( $_GET['search_feed'] ?? '' );
		if ( in_array( $search_feed, [ 'post_timeline', 'post_reviews', 'post_wall', 'user_timeline' ], true ) ) {
			$args['feed'] = $search_feed;
		}

		$search_id = is_numeric( $_GET['search_id'] ?? '' ) ? absint( $_GET['search_id'] ) : null;
		if ( $search_id !== null ) {
			$args['id'] = $search_id;
		}

		$search_user_id = is_numeric( $_GET['search_user_id'] ?? '' ) ? absint( $_GET['search_user_id'] ) : null;
		if ( $search_user_id !== null ) {
			$args['user_id'] = $search_user_id;
		}

		$search_post_id = is_numeric( $_GET['search_post_id'] ?? '' ) ? absint( $_GET['search_post_id'] ) : null;
		if ( $search_post_id !== null ) {
			$args['post_id'] = $search_post_id;
		}

		$search_query = ! empty( $_REQUEST['s'] ) && is_string( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : null;
		if ( $search_query !== null ) {
			$args['search'] = $search_query;
		}

		$query = \Voxel\Timeline\Status::query( $args );

		$this->items = $query['items'];

		$this->set_pagination_args( [
			'total_items' => $query['_total_count'],
			'per_page' => $limit,
			'total_pages' => ceil( $query['_total_count'] / $limit ),
		] );
	}
}
