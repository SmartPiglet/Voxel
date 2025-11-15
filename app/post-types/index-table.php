<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Index_Table {

	private $post_type;
	private $table_name;
	private $price_index_table_name;

	private
		$columns = [],
		$keys = [],
		$foreign_keys = [];

	private
		$price_index_columns = [],
		$price_index_keys = [],
		$price_index_foreign_keys = [];

	public function __construct( \Voxel\Post_Type $post_type ) {
		global $wpdb;
		$this->post_type = $post_type;
		$this->table_name = sprintf( '%1$svoxel_index_%2$s', $wpdb->prefix, $this->post_type->get_key() );
		$this->price_index_table_name = sprintf( '%1$svoxel_price_index_%2$s', $wpdb->prefix, $this->post_type->get_key() );
	}

	public function get_name() {
		return $this->table_name;
	}

	public function get_escaped_name() {
		return esc_sql( $this->table_name );
	}

	public function get_price_index_escaped_name() {
		return esc_sql( $this->price_index_table_name );
	}

	public function add_column( string $column_sql ) {
		if ( ! isset( $this->columns[ $column_sql ] ) ) {
			$this->columns[ $column_sql ] = $column_sql;
		}
	}

	public function add_price_index_column( string $column_sql ) {
		if ( ! isset( $this->price_index_columns[ $column_sql ] ) ) {
			$this->price_index_columns[ $column_sql ] = $column_sql;
		}
	}

	public function add_key( string $key_sql ) {
		if ( ! isset( $this->keys[ $key_sql ] ) ) {
			$this->keys[ $key_sql ] = $key_sql;
		}
	}

	public function add_price_index_key( string $key_sql ) {
		if ( ! isset( $this->price_index_keys[ $key_sql ] ) ) {
			$this->price_index_keys[ $key_sql ] = $key_sql;
		}
	}

	public function add_foreign_key( string $key_sql ) {
		if ( ! isset( $this->foreign_keys[ $key_sql ] ) ) {
			$this->foreign_keys[ $key_sql ] = $key_sql;
		}
	}

	public function add_price_index_foreign_key( string $key_sql ) {
		if ( ! isset( $this->price_index_foreign_keys[ $key_sql ] ) ) {
			$this->price_index_foreign_keys[ $key_sql ] = $key_sql;
		}
	}

	protected function _index_base_columns(): void {
		global $wpdb;

		$this->add_column( 'id INT UNSIGNED NOT NULL AUTO_INCREMENT' );
		$this->add_column( 'post_id BIGINT(20) UNSIGNED NOT NULL' );
		$this->add_column( 'post_status VARCHAR(20) NOT NULL' );
		$this->add_column( 'priority TINYINT NOT NULL DEFAULT 0' );
		$this->add_key( 'PRIMARY KEY (id)' );
		$this->add_key( 'UNIQUE KEY (post_id)' );
		$this->add_key( 'KEY (post_status)' );
		$this->add_key( 'KEY (priority)' );
		$this->add_foreign_key( sprintf( 'FOREIGN KEY (post_id) REFERENCES %s(ID) ON DELETE CASCADE', $wpdb->posts ) );
	}

	protected function _price_index_base_columns(): void {
		global $wpdb;

		$this->add_price_index_column( 'id INT UNSIGNED NOT NULL AUTO_INCREMENT' );
		$this->add_price_index_column( 'post_id BIGINT(20) UNSIGNED NOT NULL' );
		$this->add_price_index_column( 'product_type VARCHAR(64) NOT NULL' );
		$this->add_price_index_column( 'is_custom TINYINT NOT NULL DEFAULT 0' );

		$this->add_price_index_column( '`minimum_price` INT UNSIGNED NOT NULL DEFAULT 0' );
		$this->add_price_index_column( '`base_price` INT UNSIGNED NOT NULL DEFAULT 0' );

		$srid = ! \Voxel\is_using_mariadb() ? 'SRID 0' : '';
		$this->add_price_index_column( sprintf( '`days` MULTILINESTRING NOT NULL %s', $srid ) );
		$this->add_price_index_column( sprintf( '`weekdays` MULTILINESTRING NOT NULL %s', $srid ) );

		$this->add_price_index_key( 'PRIMARY KEY (id)' );
		$this->add_price_index_key( 'KEY (post_id)' );
		$this->add_price_index_key( 'KEY (product_type)' );
		$this->add_price_index_key( 'KEY (is_custom)' );
		$this->add_price_index_key( 'KEY(`minimum_price`)' );
		$this->add_price_index_key( 'KEY(`base_price`)' );
		$this->add_price_index_key( 'SPATIAL KEY(`days`)' );
		$this->add_price_index_key( 'SPATIAL KEY(`weekdays`)' );

		$this->add_price_index_foreign_key( sprintf( 'FOREIGN KEY (post_id) REFERENCES `%s`(post_id) ON DELETE CASCADE', $this->get_escaped_name() ) );
	}

	public function get_sql() {
		global $wpdb;

		$this->_index_base_columns();
		$this->_price_index_base_columns();

		$filters = $this->post_type->get_filters();

		// sort filters by key so that changing filter order doesn't affect
		// the generated index table schema
		ksort( $filters );

		foreach ( $filters as $filter ) {
			$filter->setup( $this );
		}

		$columns = "\n\t".join( ",\n\t", $this->columns ).',';
		$keys = "\n\t".join( ",\n\t", $this->keys ).',';
		$foreign_keys = "\n\t".join( ",\n\t", $this->foreign_keys );

		$sql = "CREATE TABLE IF NOT EXISTS `{$this->get_escaped_name()}` ($columns \n $keys \n $foreign_keys \n) ENGINE = InnoDB {$wpdb->get_charset_collate()};";
		return $sql;
	}

	public function get_price_index_sql() {
		global $wpdb;

		$filters = $this->post_type->get_filters();

		// sort filters by key so that changing filter order doesn't affect
		// the generated index table schema
		ksort( $filters );

		foreach ( $filters as $filter ) {
			$filter->setup( $this );
		}

		$columns = "\n\t".join( ",\n\t", $this->price_index_columns ).',';
		$keys = "\n\t".join( ",\n\t", $this->price_index_keys ).',';
		$foreign_keys = "\n\t".join( ",\n\t", $this->price_index_foreign_keys );

		$sql = "CREATE TABLE IF NOT EXISTS `{$this->get_price_index_escaped_name()}` ($columns \n $keys \n $foreign_keys \n) ENGINE = InnoDB {$wpdb->get_charset_collate()};";

		// \Voxel\log($sql);
		return $sql;
	}

	public function has_price_index_table(): bool {
		$product_field = $this->post_type->get_field( 'product' );
		if ( ! $product_field ) {
			return false;
		}

		if ( $product_field->get_type() !== 'product' ) {
			return false;
		}

		return true;
	}

	public function create() {
		global $wpdb;
		$wpdb->query( $this->get_sql() );

		if ( $this->has_price_index_table() ) {
			$wpdb->query( $this->get_price_index_sql() );
		}
	}

	public function index( $post_ids, $filters = null ) {
		global $wpdb;

		$post_ids = (array) $post_ids;
		$data = [];
		$price_index_data = [];
		$filtered_ids = [];

		foreach ( $post_ids as $i => $post_id ) {
			$post = \Voxel\Post::get( $post_id );
			if ( ! $post ) {
				continue;
			}

			$filtered_ids[] = absint( $post->get_id() );

			$data[ $i ] = [
				'post_id' => $post->get_id(),
				'post_status' => '\''.esc_sql( $post->get_status() ).'\'',
				'priority' => (int) $post->get_priority(),
			];

			foreach ( $this->post_type->get_filters() as $filter ) {
				// if a specific list of filters has been passed to index, skip on other filters
				if ( is_array( $filters ) && ! in_array( $filter->get_key(), $filters, true ) ) {
					continue;
				}

				$data[ $i ] += $filter->index( \Voxel\Post::get( $post_id ) );
			}

			if ( $this->has_price_index_table() ) {
				$product_field = $post->get_field( 'product' );
				$product_type = $product_field->get_product_type();
				foreach ( $product_field->get_prices_for_index() as $price ) {
					$price_data = [
						'post_id' => absint( $price['post_id'] ),
						'product_type' => '\''.esc_sql( $price['product_type'] ).'\'',
						'is_custom' => 0,
						'days' => 'ST_GeomFromText(\'MULTILINESTRING((-0.1 0,-0.1 0))\')',
						'weekdays' => 'ST_GeomFromText(\'MULTILINESTRING((-0.1 0,-0.1 0))\')',
					];

					if ( $price['is_custom'] ) {
						$price_data['is_custom'] = 1;
						$price_data['days'] = sprintf( 'ST_GeomFromText( \'%s\', 0 )', $price['days'] );
						$price_data['weekdays'] = sprintf( 'ST_GeomFromText( \'%s\', 0 )', $price['weekdays'] );
					}

					foreach ( $price as $price_index_column_key => $price_index_column_value ) {
						if ( ! array_key_exists( $price_index_column_key, $price_data ) ) {
							$price_data[ $price_index_column_key ] = $price_index_column_value;
						}
					}

					$price_index_data[] = $price_data;
				}
			}
		}

		if ( ! empty( $data ) ) {
			$columns = join( ', ', array_map( function( $column_name ) {
				return sprintf( '`%s`', esc_sql( $column_name ) );
			}, array_keys( $data[0] ) ) );

			$values = join( ', ', array_map( function( $row ) {
				return '('.join( ', ', $row ).')';
			}, $data ) );

			$on_duplicate = join( ', ', array_map( function( $column_name ) {
				return sprintf( '`%s`=VALUES(`%s`)', $column_name, $column_name );
			}, array_keys( $data[0] ) ) );

			$sql = "INSERT INTO `{$this->get_escaped_name()}` ($columns) VALUES $values
						ON DUPLICATE KEY UPDATE $on_duplicate";

			// dump_sql( $sql );

			$wpdb->query( $sql );
		}

		if ( $this->has_price_index_table() ) {
			$_filtered_ids = join( ',', array_map( 'absint', $filtered_ids ) );
			if ( ! empty( $_filtered_ids ) ) {
				$wpdb->query( <<<SQL
					DELETE FROM `{$this->get_price_index_escaped_name()}` WHERE post_id IN ({$_filtered_ids})
				SQL );
			}

			// \Voxel\log($price_index_data);
			if ( ! empty( $price_index_data ) ) {
				$price_index_columns = join( ', ', array_map( function( $column_name ) {
					return sprintf( '`%s`', esc_sql( $column_name ) );
				}, array_keys( $price_index_data[0] ) ) );

				$price_index_values = join( ', ', array_map( function( $row ) {
					return '('.join( ', ', $row ).')';
				}, $price_index_data ) );

				$price_index_on_duplicate = join( ', ', array_map( function( $column_name ) {
					return sprintf( '`%s`=VALUES(`%s`)', $column_name, $column_name );
				}, array_keys( $price_index_data[0] ) ) );

				$price_index_sql = "INSERT INTO `{$this->get_price_index_escaped_name()}` ($price_index_columns) VALUES $price_index_values
							ON DUPLICATE KEY UPDATE $price_index_on_duplicate";

				// \Voxel\log( $price_index_sql );

				$wpdb->query( $price_index_sql );
			}
		}
	}

	public function unindex( $post_ids ) {
		global $wpdb;
		$post_ids = array_map( 'absint', (array) $post_ids );
		if ( empty( $post_ids ) ) {
			return;
		}

		$ids = join( ',', $post_ids );
		$sql = "DELETE FROM `{$this->get_escaped_name()}` WHERE post_id IN ({$ids})";
		$wpdb->query( $sql );
	}

	public function truncate() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE `{$this->get_escaped_name()}`" );
	}

	public function drop() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS `{$this->get_price_index_escaped_name()}`" );
		$wpdb->query( "DROP TABLE IF EXISTS `{$this->get_escaped_name()}`" );
	}

	public function recreate() {
		$this->drop();
		$this->create();
	}

	public function exists(): bool {
		global $wpdb;
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$this->get_escaped_name()}'" ) ) {
			return false;
		}

		// post_status became a mandatory column later on, if missing, always recreate table
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$this->get_escaped_name()}` LIKE 'post_status'" ) ) {
			return false;
		}

		// priority became a mandatory column later on, if missing, always recreate table
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$this->get_escaped_name()}` LIKE 'priority'" ) ) {
			return false;
		}

		if ( $this->has_price_index_table() ) {
			if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$this->get_price_index_escaped_name()}'" ) ) {
				return false;
			}
		}

		return true;
	}
}
