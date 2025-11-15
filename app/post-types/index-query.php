<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Index_Query {

	public
		$post_type,
		$table;

	private
		$select_clauses = [],
		$join_clauses = [],
		$where_clauses = [],
		$orderby_clauses = [],
		$groupby_clauses = [],
		$post_statuses = ['publish'];

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
		$this->table = $post_type->get_index_table();
	}

	public function select( string $clause_sql ) {
		$this->select_clauses[] = $clause_sql;
	}

	public function join( string $clause_sql ) {
		$this->join_clauses[] = $clause_sql;
	}

	public function where( string $clause_sql ) {
		$this->where_clauses[] = $clause_sql;
	}

	public function orderby( string $clause_sql ) {
		$this->orderby_clauses[] = $clause_sql;
	}

	public function groupby( string $clause_sql ) {
		$this->groupby_clauses[] = $clause_sql;
	}

	public function set_post_statuses( array $statuses ) {
		$this->post_statuses = $statuses;
	}

	public function get_sql( array $args = [], $cb = null ) {
		// reset
		$this->select_clauses = [];
		$this->join_clauses = [];
		$this->where_clauses = [];
		$this->orderby_clauses = [];
		$this->groupby_clauses = [];
		$this->post_statuses = ['publish'];

		// apply filters
		foreach ( $this->post_type->get_filters() as $filter ) {
			$filter->query( $this, $args );
		}

		$limit = '';
		if ( isset( $args['limit'] ) && absint( $args['limit'] ) > 0 ) {
			$limit = sprintf( 'LIMIT %d', absint( $args['limit'] ) );
		}

		$offset = '';
		if ( isset( $args['offset'] ) && absint( $args['offset'] ) > 0 ) {
			$offset = sprintf( 'OFFSET %d', absint( $args['offset'] ) );
		}

		if ( is_callable( $cb ) ) {
			$cb( $this, $args );
		}

		// generate sql string
		$sql = "
			SELECT DISTINCT `{$this->table->get_escaped_name()}`.post_id {$this->_get_select_clauses()}
				FROM `{$this->table->get_escaped_name()}`
			{$this->_get_join_clauses()}
			{$this->_get_where_clauses()}
			{$this->_get_groupby_clauses()}
			{$this->_get_orderby_clauses()}
			{$limit} {$offset}
		";

		return $sql;
	}

	public function get_count_sql( array $args = [], $cb = null ) {
		// reset
		$this->select_clauses = [];
		$this->join_clauses = [];
		$this->where_clauses = [];
		$this->orderby_clauses = [];
		$this->groupby_clauses = [];
		$this->post_statuses = ['publish'];

		// apply filters
		foreach ( $this->post_type->get_filters() as $filter ) {
			$filter->query( $this, $args );
		}

		$limit = '';
		if ( isset( $args['limit'] ) && absint( $args['limit'] ) > 0 ) {
			$limit = sprintf( 'LIMIT %d', absint( $args['limit'] ) );
		}

		$offset = '';
		if ( isset( $args['offset'] ) && absint( $args['offset'] ) > 0 ) {
			$offset = sprintf( 'OFFSET %d', absint( $args['offset'] ) );
		}

		if ( is_callable( $cb ) ) {
			$cb( $this, $args );
		}

		// generate sql string
		$sql = "
			SELECT COUNT( DISTINCT `{$this->table->get_escaped_name()}`.post_id )
				FROM `{$this->table->get_escaped_name()}`
			{$this->_get_join_clauses()}
			{$this->_get_where_clauses()}
			LIMIT 1
		";

		return $sql;
	}

	public function join_price_index( array $args ) {
		$date = \Voxel\now();

		// use first day of availability filter (if present)
		foreach ( $this->post_type->get_filters() as $filter ) {
			if ( $filter->get_type() === 'availability' && $filter->get_prop('source') === 'product' ) {
				$filter_value = $filter->parse_value( $args[ $filter->get_key() ] ?? null );
				if ( $filter_value !== null ) {
					if ( ! ( strtotime( $filter_value['start'] ?? '' ) && strtotime( $filter_value['end'] ?? '' ) ) ) {
						break;
					}

					$date = new \DateTime( date( 'Y-m-d 00:00:00', strtotime( $filter_value['start'] ) ), wp_timezone() );
					break;
				}
			}
		}

		$day = date_diff( \Voxel\epoch(), $date )->days;
		$days_ls = sprintf( 'POINT(%s 0)', absint( $day ) );
		$weekdays_ls = sprintf( 'POINT(%s 0)', absint( $date->format('N') - 1 ) );

		$this->join( <<<SQL
			INNER JOIN (
				SELECT *, ROW_NUMBER() OVER(PARTITION BY post_id ORDER BY id ASC) AS rn
				FROM `{$this->table->get_price_index_escaped_name()}`
				WHERE is_custom = 0 OR ( is_custom = 1 AND (
					ST_Intersects( ST_GeomFromText( '{$days_ls}', 0 ), `days` )
					OR ST_Intersects( ST_GeomFromText( '{$weekdays_ls}', 0 ), `weekdays` )
				) )
			) AS pricing ON `{$this->table->get_escaped_name()}`.post_id = pricing.post_id AND pricing.rn = 1
		SQL );
	}

	public function get_minimum_price_query( array $args ) {
		$clauses = [];
		foreach ( $this->post_type->get_filters() as $filter ) {
			if ( $filter->get_type() === 'stepper' ) {
				$parts = explode( '->', $filter->get_prop('source') );
				if ( ( $parts[0] ?? null ) === 'product' && ( $parts[1] ?? null ) === 'addons' && isset( $parts[2] ) && isset( $parts[3] ) ) {
					$filter_value = $filter->parse_value( $args[ $filter->get_key() ] ?? null );
					if ( $filter_value !== null ) {
						$db_key__price = sprintf( 'pricing.`%s_addon_%s_price`', $parts[2], $parts[3] );
						$clauses[] = sprintf( '( %s * %d )', esc_sql( $db_key__price ), esc_sql( $filter_value ) );
					}
				}
			} elseif ( $filter->get_type() === 'switcher' ) {
				$parts = explode( '->', $filter->get_prop('source') );
				if ( ( $parts[0] ?? null ) === 'product' && ( $parts[1] ?? null ) === 'addons' && isset( $parts[2] ) && isset( $parts[3] ) ) {
					$filter_value = $filter->parse_value( $args[ $filter->get_key() ] ?? null );
					if ( $filter_value !== null ) {
						$db_key__price = sprintf( 'pricing.`%s_addon_%s_price`', $parts[2], $parts[3] );
						$clauses[] = sprintf( '%s', esc_sql( $db_key__price ) );
					}
				}
			}
		}

		if ( ! empty( $clauses ) ) {
			array_unshift( $clauses, 'pricing.base_price' );
			return sprintf( '( %s )', join( ' + ', $clauses ) );
		}

		return 'pricing.minimum_price';
	}

	private function _get_where_clauses() {
		$clauses = $this->where_clauses;

		$indexable_statuses = $this->post_type->repository->get_indexable_statuses();
		if ( ! empty( $this->post_statuses ) && count( $indexable_statuses ) > 1 ) {
			$statuses = array_filter( array_map( 'esc_sql', $this->post_statuses ) );
			$statuses = array_values( $statuses );
			if ( count( $statuses ) === 1 ) {
				array_unshift( $clauses, sprintf( "`{$this->table->get_escaped_name()}`.post_status = '%s'", $statuses[0] ) );
			} elseif ( count( $statuses ) >= 1 ) {
				array_unshift( $clauses, sprintf( "`{$this->table->get_escaped_name()}`.post_status IN (%s)", "'".join( "','", $statuses )."'" ) );
			}
		}

		if ( empty( $clauses ) ) {
			return '';
		}

		return sprintf( 'WHERE %s', join( ' AND ', $clauses ) );
	}

	private function _get_select_clauses() {
		if ( empty( $this->select_clauses ) ) {
			return '';
		}

		return ', '. join( ", ", array_unique( $this->select_clauses ) );
	}

	private function _get_join_clauses() {
		if ( empty( $this->join_clauses ) ) {
			return '';
		}

		return join( " \n ", array_unique( $this->join_clauses ) );
	}

	private function _get_orderby_clauses() {
		if ( empty( $this->orderby_clauses ) ) {
			return 'ORDER BY priority DESC, post_id DESC';
		}

		return sprintf( 'ORDER BY %s, post_id DESC', join( ", ", array_unique( $this->orderby_clauses ) ) );
	}

	private function _get_groupby_clauses() {
		if ( empty( $this->groupby_clauses ) ) {
			return '';
		}

		return sprintf( 'GROUP BY %s', join( ", ", array_unique( $this->groupby_clauses ) ) );
	}

	public function get_posts( array $args = [], $cb = null ) {
		global $wpdb;

		// workaround to https://jira.mariadb.org/browse/MDEV-26123
		if ( \Voxel\is_using_mariadb() ) {
			$wpdb->query( 'SET autocommit = 0;' );
		}

		// dump_sql($this->get_sql( $args ));
		$post_ids = $wpdb->get_col( $this->get_sql( $args, $cb ) );
		// dump_sql( $this->get_sql( $args ) );
		// dump_sql( $this->get_count_sql( $args ) );

		if ( \Voxel\is_using_mariadb() ) {
			$wpdb->query( 'SET autocommit = 1;' );
		}

		return array_map( 'intval', $post_ids );
	}

	public function get_post_count( array $args = [], $cb = null ) {
		global $wpdb;

		// workaround to https://jira.mariadb.org/browse/MDEV-26123
		if ( \Voxel\is_using_mariadb() ) {
			$wpdb->query( 'SET autocommit = 0;' );
		}

		$count = $wpdb->get_var( $this->get_count_sql( $args, $cb ) );

		if ( \Voxel\is_using_mariadb() ) {
			$wpdb->query( 'SET autocommit = 1;' );
		}

		return is_numeric( $count ) ? absint( $count ) : 0;
	}

	public function get_adaptive_counts_for_terms_filter( $terms_filter, array $args = [] ) {
		// reset
		$this->select_clauses = [];
		$this->join_clauses = [];
		$this->where_clauses = [];
		$this->orderby_clauses = [];
		$this->groupby_clauses = [];
		$this->post_statuses = ['publish'];

		// apply filters
		foreach ( $this->post_type->get_filters() as $filter ) {
			if ( $filter->get_key() === $terms_filter->get_key() ) {
				if ( ! ( $filter->get_prop('multiple') && $filter->get_prop('operator') === 'and' ) ) {
					continue;
				}
			}

			$filter->query( $this, $args );
		}

		// generate sql string
		$sql = "
			SELECT DISTINCT `{$this->table->get_escaped_name()}`.post_id {$this->_get_select_clauses()}
				FROM `{$this->table->get_escaped_name()}`
			{$this->_get_join_clauses()}
			{$this->_get_where_clauses()}
			{$this->_get_groupby_clauses()}
		";

		return $sql;
	}

	public function get_adaptive_query_for_range_filter( $range_filter, array $args = [] ) {
		// reset
		$this->select_clauses = [];
		$this->join_clauses = [];
		$this->where_clauses = [];
		$this->orderby_clauses = [];
		$this->groupby_clauses = [];
		$this->post_statuses = ['publish'];

		// apply filters
		foreach ( $this->post_type->get_filters() as $filter ) {
			if ( $filter->get_key() === $range_filter->get_key() ) {
				continue;
			}

			$filter->query( $this, $args );
		}

		$source = $range_filter->get_prop('source');
		if ( $source === 'product->:minimum_price' ) {
			$column_key = "`pricing`.`minimum_price` / 100";
			$this->join_price_index( $args );
		} elseif ( $source === ':post->priority' ) {
			$column_key = '`priority`';
		} else {
			$column_key = '`'.esc_sql( $range_filter->db_key() ).'`';
		}

		// generate sql string
		$sql = "
			SELECT MIN({$column_key}) AS min_value, MAX({$column_key}) AS max_value
				FROM `{$this->table->get_escaped_name()}`
			{$this->_get_join_clauses()}
			{$this->_get_where_clauses()}
			{$this->_get_groupby_clauses()}
		";

		return $sql;
	}
}
