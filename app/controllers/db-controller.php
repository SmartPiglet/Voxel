<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Db_Controller extends Base_Controller {

	protected function hooks() {
		$this->on( 'after_setup_theme', '@prepare_db', 0 );
		$this->on( 'after_setup_theme', '@create_recurring_dates_table', 0 );
		$this->on( 'after_setup_theme', '@create_orders_table', 0 );
		$this->on( 'after_setup_theme', '@create_timeline_table', 0 );
		$this->on( 'after_setup_theme', '@create_timeline_status_likes_table', 0 );
		$this->on( 'after_setup_theme', '@create_timeline_replies_table', 0 );
		$this->on( 'after_setup_theme', '@create_followers_table', 0 );
		$this->on( 'after_setup_theme', '@create_work_hours_table', 0 );
		$this->on( 'after_setup_theme', '@create_post_relations_table', 0 );
		$this->on( 'after_setup_theme', '@create_notifications_table', 0 );
		$this->on( 'after_setup_theme', '@create_messages_table', 0 );
		$this->on( 'after_setup_theme', '@create_visits_table', 0 );
		$this->on( 'after_setup_theme', '@modify_terms_table', 0 );
		$this->on( 'after_setup_theme', '@modify_posts_table', 0 );
		$this->on( 'after_setup_theme', '@modify_users_table', 0 );
		$this->on( 'after_setup_theme', '@create_auth_codes_table', 0 );
		$this->on( 'after_setup_theme', '@migrate_taxonomy_templates', 0 );
	}

	protected function prepare_db() {
		$db_version = '0.15';
		$current_version = \Voxel\get( 'versions.db' );
		if ( $db_version === $current_version ) {
			return;
		}

		global $wpdb;

		// wp_posts must use InnoDB
		$wp_posts = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS WHERE name = %s", $wpdb->posts ) );
		$wp_posts_engine = $wp_posts->Engine ?? null;
		if ( $wp_posts_engine !== 'InnoDB' ) {
			$wpdb->query( "ALTER TABLE {$wpdb->posts} ENGINE = InnoDB;" );
		}

		// wp_users must use InnoDB
		$wp_users = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS WHERE name = %s", $wpdb->users ) );
		$wp_users_engine = $wp_users->Engine ?? null;
		if ( $wp_users_engine !== 'InnoDB' ) {
			$wpdb->query( "ALTER TABLE {$wpdb->users} ENGINE = InnoDB;" );
		}

		\Voxel\set( 'versions.db', $db_version );
	}

	protected function create_recurring_dates_table() {
		$table_version = '0.2';
		$current_version = \Voxel\get( 'versions.recurring_dates_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create events table
		$table_name = $wpdb->prefix . 'voxel_recurring_dates';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`post_id` BIGINT(20) UNSIGNED NOT NULL,
				`post_type` VARCHAR(64) NOT NULL,
				`field_key` VARCHAR(64) NOT NULL,
				`start` DATETIME NOT NULL,
				`end` DATETIME NOT NULL,
				`frequency` SMALLINT UNSIGNED,
				`unit` ENUM('day','month'),
				`until` DATETIME,
				PRIMARY KEY (`id`),
					KEY (`post_id`),
					KEY (`post_type`),
					KEY (`field_key`),
					KEY (`start`),
					KEY (`end`),
					KEY (`frequency`),
					KEY (`unit`),
					KEY (`until`),
				FOREIGN KEY (`post_id`)
					REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		// migrate data from old events table
		if ( \Voxel\get('versions.events_table') === '0.14' && ( !! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}voxel_events'" ) ) ) {
			add_action( 'init', function() {
				global $wpdb;
				$results = $wpdb->get_results( <<<SQL
					SELECT post_id, field_key, CONCAT( '[', GROUP_CONCAT( details SEPARATOR ',' ), ']' ) AS details
					FROM `{$wpdb->prefix}voxel_events`
					GROUP BY post_id, field_key
				SQL );

				foreach ( $results as $result ) {
					$value = json_decode( $result->details, ARRAY_A );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						continue;
					}

					$post = \Voxel\Post::get( $result->post_id );
					$field = $post ? $post->get_field( $result->field_key ) : null;
					if ( $field && $field->get_type() === 'recurring-date' ) {
						$field->update( $value );
					}
				}

				// $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}voxel_events`" );
				\Voxel\get('versions.events_table', null);
			} );
		}

		\Voxel\set( 'versions.recurring_dates_table', $table_version );
	}

	protected function create_orders_table() {
		$table_version = '0.41';
		$current_version = \Voxel\get( 'versions.orders_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'vx_orders';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT(20) UNSIGNED NOT NULL,
				vendor_id BIGINT(20) UNSIGNED,
				status VARCHAR(32) NOT NULL,
				shipping_status VARCHAR(32) DEFAULT NULL,
				payment_method VARCHAR(64) NOT NULL,
				transaction_id VARCHAR(128),
				details MEDIUMTEXT,
				parent_id BIGINT(20) UNSIGNED,
				testmode BOOLEAN NOT NULL DEFAULT false,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
					KEY (customer_id),
					KEY (vendor_id),
					KEY (status),
					KEY (shipping_status),
					KEY (payment_method),
					KEY (transaction_id),
					KEY (parent_id),
					KEY (testmode),
					KEY (created_at),
				FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}vx_orders(id) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'vx_order_items';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				order_id BIGINT(20) UNSIGNED NOT NULL,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				product_type VARCHAR(64) NOT NULL,
				field_key VARCHAR(64) NOT NULL,
				details MEDIUMTEXT,
				PRIMARY KEY (id),
					KEY (order_id),
					KEY (post_id),
					KEY (product_type),
					KEY (field_key),
				FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}vx_orders(id) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		$parent_order_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}vx_orders LIKE 'parent_id'" );
		if ( ! $parent_order_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}vx_orders ADD COLUMN `parent_id` BIGINT(20) UNSIGNED AFTER `details`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}vx_orders ADD KEY (`parent_id`)" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}vx_orders ADD FOREIGN KEY (`parent_id`) REFERENCES {$wpdb->prefix}vx_orders(`id`) ON DELETE CASCADE" );
		}

		$shipping_status_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}vx_orders LIKE 'shipping_status'" );
		if ( ! $shipping_status_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}vx_orders ADD COLUMN `shipping_status` VARCHAR(32) DEFAULT NULL AFTER `status`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}vx_orders ADD KEY (`shipping_status`)" );
		}

		\Voxel\set( 'versions.orders_table', $table_version );
	}

	protected function create_timeline_table() {
		$table_version = '0.31';
		$current_version = \Voxel\get( 'versions.timeline_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create statuses table
		$table_name = $wpdb->prefix . 'voxel_timeline';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` BIGINT(20) UNSIGNED,
				`published_as` BIGINT(20) UNSIGNED,
				`post_id` BIGINT(20) UNSIGNED,
				`content` TEXT,
				`details` TEXT,
				`review_score` DECIMAL(3,2),
				`moderation` TINYINT NOT NULL DEFAULT 1,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`edited_at` DATETIME,
				PRIMARY KEY (`id`),
					KEY (`user_id`),
					KEY (`post_id`),
					KEY (`published_as`),
					KEY (`review_score`),
					KEY (`created_at`),
					KEY (`moderation`),
				FOREIGN KEY (`user_id`) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`published_as`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`post_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		$feed_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'feed'" );
		if ( ! $feed_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `feed` ENUM('post_reviews','post_wall','post_timeline','user_timeline') AFTER `post_id`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`feed`)" );
		}

		$repost_of_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'repost_of'" );
		if ( ! $repost_of_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `repost_of` BIGINT(20) UNSIGNED AFTER `details`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD FOREIGN KEY (`repost_of`) REFERENCES {$wpdb->prefix}voxel_timeline(id) ON DELETE CASCADE" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`repost_of`)" );
		}

		$quote_of_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'quote_of'" );
		if ( ! $quote_of_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `quote_of` BIGINT(20) UNSIGNED AFTER `details`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`quote_of`)" );
		}

		$like_count_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'like_count'" );
		if ( ! $like_count_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `like_count` MEDIUMINT NOT NULL DEFAULT 0 AFTER `edited_at`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`like_count`)" );
		}

		$reply_count_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'reply_count'" );
		if ( ! $reply_count_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `reply_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER `like_count`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`reply_count`)" );
		}

		$_index_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE '_index'" );
		if ( ! $_index_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `_index` TEXT AFTER `reply_count`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD FULLTEXT (`_index`)" );
		}

		$moderation_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline LIKE 'moderation'" );
		if ( ! $moderation_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD COLUMN `moderation` TINYINT NOT NULL DEFAULT 1 AFTER `review_score`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline ADD KEY (`moderation`)" );
		}

		if ( in_array( \Voxel\get( 'versions.timeline_table' ), ['0.20', '0.24'] ) ) {
			$wpdb->query( <<<SQL
				UPDATE {$wpdb->prefix}voxel_timeline AS tl
				LEFT JOIN {$wpdb->usermeta} AS meta ON ( meta.user_id = tl.user_id AND meta.meta_key = 'voxel:profile_id' )
				SET tl.feed = 'user_timeline', tl.published_as = NULL, tl.post_id = NULL
				WHERE tl.feed IS NULL
					AND tl.review_score IS NULL
					AND tl.user_id IS NOT NULL
					AND ( tl.post_id IS NULL OR tl.post_id = meta.meta_value );
			SQL );

			$wpdb->query( <<<SQL
				UPDATE {$wpdb->prefix}voxel_timeline
				SET feed = 'post_wall', published_as = NULL
				WHERE feed IS NULL
					AND review_score IS NULL
					AND user_id IS NOT NULL
					AND post_id IS NOT NULL;
			SQL );

			$wpdb->query( <<<SQL
				UPDATE {$wpdb->prefix}voxel_timeline
				SET feed = 'post_timeline', user_id = NULL, published_as = NULL
				WHERE feed IS NULL
					AND review_score IS NULL
					AND published_as IS NOT NULL
					AND post_id IS NOT NULL
					AND published_as = post_id;
			SQL );

			$wpdb->query( <<<SQL
				UPDATE {$wpdb->prefix}voxel_timeline
				SET feed = 'post_reviews', published_as = NULL
				WHERE feed IS NULL
					AND review_score IS NOT NULL
					AND user_id IS NOT NULL
					AND post_id IS NOT NULL;
			SQL );
		}

		\Voxel\set( 'versions.timeline_table', $table_version );
	}

	protected function create_timeline_status_likes_table() {
		$table_version = '0.1';
		$current_version = \Voxel\get( 'versions.timeline_status_likes_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create status likes table
		$table_name = $wpdb->prefix . 'voxel_timeline_status_likes';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` BIGINT(20) UNSIGNED,
				`post_id` BIGINT(20) UNSIGNED,
				`status_id` BIGINT(20) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
					KEY (`user_id`),
					KEY (`post_id`),
					KEY (`status_id`),
				FOREIGN KEY (`user_id`) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`post_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`status_id`) REFERENCES {$wpdb->prefix}voxel_timeline(id) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		\Voxel\set( 'versions.timeline_status_likes_table', $table_version );
	}

	protected function create_timeline_replies_table() {
		$table_version = '0.7';
		$current_version = \Voxel\get( 'versions.timeline_replies_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create replies table
		$table_name = $wpdb->prefix . 'voxel_timeline_replies';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` BIGINT(20) UNSIGNED,
				`published_as` BIGINT(20) UNSIGNED,
				`status_id` BIGINT(20) UNSIGNED NOT NULL,
				`parent_id` BIGINT(20) UNSIGNED,
				`content` TEXT,
				`details` TEXT,
				`moderation` TINYINT NOT NULL DEFAULT 1,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`edited_at` DATETIME,
				PRIMARY KEY (`id`),
					KEY (`user_id`),
					KEY (`status_id`),
					KEY (`parent_id`),
					KEY (`created_at`),
					KEY (`moderation`),
				FOREIGN KEY (`user_id`) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`published_as`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`status_id`) REFERENCES {$wpdb->prefix}voxel_timeline(id) ON DELETE CASCADE,
				FOREIGN KEY (`parent_id`) REFERENCES {$wpdb->prefix}voxel_timeline_replies(id) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		$like_count_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline_replies LIKE 'like_count'" );
		if ( ! $like_count_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD COLUMN `like_count` MEDIUMINT NOT NULL DEFAULT 0 AFTER `edited_at`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD KEY (`like_count`)" );
		}

		$reply_count_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline_replies LIKE 'reply_count'" );
		if ( ! $reply_count_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD COLUMN `reply_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER `like_count`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD KEY (`reply_count`)" );
		}

		$moderation_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline_replies LIKE 'moderation'" );
		if ( ! $moderation_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD COLUMN `moderation` TINYINT NOT NULL DEFAULT 1 AFTER `details`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD KEY (`moderation`)" );
		}

		$_index_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}voxel_timeline_replies LIKE '_index'" );
		if ( ! $_index_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD COLUMN `_index` TEXT AFTER `reply_count`" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_replies ADD FULLTEXT (`_index`)" );
		}

		// create reply likes table
		$table_name = $wpdb->prefix . 'voxel_timeline_reply_likes_v2';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` BIGINT(20) UNSIGNED,
				`post_id` BIGINT(20) UNSIGNED,
				`reply_id` BIGINT(20) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
					KEY (`user_id`),
					KEY (`post_id`),
					KEY (`reply_id`),
				FOREIGN KEY (`user_id`) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`post_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`reply_id`) REFERENCES {$wpdb->prefix}voxel_timeline_replies(id) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		/*if ( $current_version === '0.3' ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_reply_likes_v2 DROP FOREIGN KEY {$wpdb->prefix}voxel_timeline_reply_likes_v2_ibfk_3" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}voxel_timeline_reply_likes_v2 ADD FOREIGN KEY (`reply_id`)
				REFERENCES {$wpdb->prefix}voxel_timeline_replies(`id`) ON DELETE CASCADE" );
		}*/

		\Voxel\set( 'versions.timeline_replies_table', $table_version );
	}

	protected function create_followers_table() {
		$table_version = '0.3';
		$current_version = \Voxel\get( 'versions.followers_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create followers table
		$table_name = $wpdb->prefix . 'voxel_followers';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`object_type` ENUM('user','post') NOT NULL,
				`object_id` BIGINT(20) UNSIGNED NOT NULL,
				`follower_type` ENUM('user','post') NOT NULL,
				`follower_id` BIGINT(20) UNSIGNED NOT NULL,
				`status` TINYINT NOT NULL,
				PRIMARY KEY (`object_type`, `object_id`, `follower_type`, `follower_id`),
					KEY (`object_type`, `object_id`),
					KEY (`follower_type`, `follower_id`),
					KEY (`status`)
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		// migrate data from voxel_followers_post and voxel_followers_user table
		if ( \Voxel\get('versions.followers_table') === '0.1' ) {
			if ( !! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}voxel_followers_user'" ) ) {
				add_action( 'init', function() {
					global $wpdb;
					$wpdb->query( <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_followers (`object_type`, `object_id`, `follower_type`, `follower_id`, `status`)
							SELECT 'user' AS `object_type`, user_id AS `object_id`, 'user' AS `follower_type`, `follower_id`, `status`
							FROM {$wpdb->prefix}voxel_followers_user
					SQL );

					// $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}voxel_followers_user`" );
					\Voxel\get('versions.followers_table', null);
				} );
			}

			if ( !! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}voxel_followers_post'" ) ) {
				add_action( 'init', function() {
					global $wpdb;
					$wpdb->query( <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_followers (`object_type`, `object_id`, `follower_type`, `follower_id`, `status`)
							SELECT 'post' AS `object_type`, post_id AS `object_id`, 'user' AS `follower_type`, `follower_id`, `status`
							FROM {$wpdb->prefix}voxel_followers_post
					SQL );

					// $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}voxel_followers_post`" );
					\Voxel\get('versions.followers_table', null);
				} );
			}
		}

		\Voxel\set( 'versions.followers_table', $table_version );
	}

	protected function create_work_hours_table() {
		$table_version = '0.2';
		$current_version = \Voxel\get( 'versions.work_hours_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'voxel_work_hours';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`post_id` BIGINT(20) UNSIGNED NOT NULL,
				`post_type` VARCHAR(64) NOT NULL,
				`field_key` VARCHAR(64) NOT NULL,
				`start` SMALLINT(5) NOT NULL,
				`end` SMALLINT(5) NOT NULL,
				PRIMARY KEY (`id`),
					KEY (`post_id`),
					KEY (`post_type`),
					KEY (`field_key`),
					KEY (`start`),
					KEY (`end`),
				FOREIGN KEY (`post_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		if ( $current_version === '0.1' ) {
			$wpdb->query( "ALTER TABLE {$table_name} CHANGE `start` `start` SMALLINT NOT NULL" );
			$wpdb->query( "ALTER TABLE {$table_name} CHANGE `end` `end` SMALLINT NOT NULL" );
		}

		\Voxel\set( 'versions.work_hours_table', $table_version );
	}

	protected function create_post_relations_table() {
		$table_version = '0.4';
		$current_version = \Voxel\get( 'versions.post_relations_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'voxel_relations';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`parent_id` BIGINT(20) UNSIGNED NOT NULL,
				`child_id` BIGINT(20) UNSIGNED NOT NULL,
				`relation_key` varchar(96) NOT NULL,
				`order` INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
				KEY (`parent_id`),
				KEY (`child_id`),
				KEY (`relation_key`),
				FOREIGN KEY (`parent_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
				FOREIGN KEY (`child_id`) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		\Voxel\set( 'versions.post_relations_table', $table_version );
	}

	protected function create_notifications_table() {
		$table_version = '0.1';
		$current_version = \Voxel\get( 'versions.notifications_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create events table
		$table_name = $wpdb->prefix . 'voxel_notifications';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` BIGINT(20) UNSIGNED NOT NULL,
				`type` VARCHAR(96) NOT NULL,
				`details` TEXT,
				`seen` TINYINT NOT NULL,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
					KEY (`user_id`),
					KEY (`type`),
					KEY (`seen`),
					KEY (`created_at`),
				FOREIGN KEY (`user_id`) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		\Voxel\set( 'versions.notifications_table', $table_version );
	}

	protected function create_messages_table() {
		$table_version = '0.3';
		$current_version = \Voxel\get( 'versions.messages_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create chats table
		// @todo: maybe index p1_cleared_below, p2_cleared_below
		$table_name = $wpdb->prefix . 'voxel_chats';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`p1_type` ENUM('user','post') NOT NULL,
				`p1_id` BIGINT(20) UNSIGNED NOT NULL,
				`p1_last_message_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				`p1_cleared_below` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				`p2_type` ENUM('user','post') NOT NULL,
				`p2_id` BIGINT(20) UNSIGNED NOT NULL,
				`p2_last_message_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				`p2_cleared_below` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				`last_message_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				`details` TEXT,
				PRIMARY KEY (`id`),
					KEY (`p1_type`, `p1_id`),
					KEY (`p2_type`, `p2_id`),
					KEY (`p1_last_message_id`),
					KEY (`p2_last_message_id`),
					KEY (`last_message_id`)
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		// create messages table
		$table_name = $wpdb->prefix . 'voxel_messages';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`sender_type` ENUM('user','post') NOT NULL,
				`sender_id` BIGINT(20) UNSIGNED NOT NULL,
				`sender_deleted` TINYINT NOT NULL DEFAULT 0,
				`receiver_type` ENUM('user','post') NOT NULL,
				`receiver_id` BIGINT(20) UNSIGNED NOT NULL,
				`receiver_deleted` TINYINT NOT NULL DEFAULT 0,
				`content` TEXT,
				`details` TEXT,
				`seen` TINYINT NOT NULL DEFAULT 0,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
					KEY (`sender_type`, `sender_id`),
					KEY (`sender_deleted`),
					KEY (`receiver_type`, `receiver_id`),
					KEY (`receiver_deleted`),
					KEY (`seen`),
					KEY (`created_at`)
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		\Voxel\set( 'versions.messages_table', $table_version );
	}

	protected function create_visits_table() {
		$table_version = '0.6';
		$current_version = \Voxel\get( 'versions.visits_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'voxel_visits';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`post_id` BIGINT(20) UNSIGNED NOT NULL,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`unique_id` VARCHAR(16) NOT NULL,
				`ip_address` VARCHAR(45),
				`ref_url` VARCHAR(128),
				`ref_domain` VARCHAR(64),
				`os` ENUM('windows','macos','linux','ubuntu','ios','android','webos'),
				`device` ENUM('mobile','desktop'),
				`browser` ENUM('chrome','firefox','safari','edge','opera','ie'),
				`country_code` VARCHAR(2),
				PRIMARY KEY (`id`),
					KEY(`post_id`),
					KEY(`created_at`),
					KEY(`unique_id`),
					KEY(`ref_url`),
					KEY(`ref_domain`),
					KEY(`os`),
					KEY(`device`),
					KEY(`browser`),
					KEY(`country_code`)
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;

		dbDelta( $sql );

		\Voxel\set( 'versions.visits_table', $table_version );
	}

	protected function modify_terms_table() {
		$table_version = '0.2';
		$current_version = \Voxel\get( 'versions.terms_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;

		$order_col_exists = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->terms} LIKE 'voxel_order'" );
		if ( ! $order_col_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->terms} ADD COLUMN `voxel_order` INT NOT NULL DEFAULT 0" );
		}

		$fulltext_exists = $wpdb->query( "SHOW INDEX FROM {$wpdb->terms} WHERE Key_name = 'vx_fulltext'" );
		if ( ! $fulltext_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->terms} ADD FULLTEXT vx_fulltext (name)" );
		}

		\Voxel\set( 'versions.terms_table', $table_version );
	}

	protected function modify_posts_table() {
		$table_version = '0.1';
		$current_version = \Voxel\get( 'versions.posts_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;

		$fulltext_exists = $wpdb->query( "SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'vx_post_title'" );
		if ( ! $fulltext_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT vx_post_title (post_title)" );
		}

		\Voxel\set( 'versions.posts_table', $table_version );
	}

	protected function modify_users_table() {
		$table_version = '0.4';
		$current_version = \Voxel\get( 'versions.users_table' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;

		$fulltext_exists = $wpdb->query( "SHOW INDEX FROM {$wpdb->users} WHERE Key_name = 'vx_display_name'" );
		if ( ! $fulltext_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->users} ADD FULLTEXT vx_display_name (display_name)" );
		}

		$display_name_index_exists = $wpdb->query( "SHOW INDEX FROM {$wpdb->users} WHERE Key_name = 'vx_display_name_key'" );
		if ( ! $display_name_index_exists ) {
			$wpdb->query( "ALTER TABLE {$wpdb->users} ADD KEY vx_display_name_key (display_name)" );
		}

		\Voxel\set( 'versions.users_table', $table_version );
	}

	protected function create_auth_codes_table() {
		$table_version = '0.7';
		$current_version = \Voxel\get( 'versions.auth_codes' );
		if ( $table_version === $current_version ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'voxel_auth_codes';
		$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS $table_name (
				`user_login` VARCHAR(60) NOT NULL,
				`code` VARCHAR(32) NOT NULL,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`user_login`),
				KEY (`code`),
				KEY (`created_at`)
			) ENGINE = InnoDB {$wpdb->get_charset_collate()};
		SQL;
		dbDelta( $sql );

		\Voxel\set( 'versions.auth_codes', $table_version );
	}

	protected function migrate_taxonomy_templates() {
		$table_version = '0.7';
		$current_version = \Voxel\get( 'versions.taxonomy_templates' );
		if ( $table_version === $current_version ) {
			return;
		}

		$custom_templates = \Voxel\get_custom_templates();

		foreach ( \Voxel\Taxonomy::get_voxel_taxonomies() as $taxonomy ) {
			$config = $taxonomy->get_config();
			if ( is_numeric( $config['templates']['single'] ?? null ) && ! empty( $config['templates']['single'] ) ) {
				$custom_templates['term_single'][] = [
					'label' => $taxonomy->get_label(),
					'id' => absint( $config['templates']['single'] ),
					'visibility_rules' => [
						[
							[
								'type' => 'template:is_single_term',
								'taxonomy' => $taxonomy->get_key(),
								'term_id' => '',
							]
						],
					],
				];
			}
		}

		\Voxel\set( 'custom_templates', $custom_templates );
		\Voxel\set( 'versions.taxonomy_templates', $table_version );
	}
}
