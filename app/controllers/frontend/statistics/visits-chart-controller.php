<?php

namespace Voxel\Controllers\Frontend\Statistics;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Visits_Chart_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_tracking.get_chart_data', '@get_chart_data' );
		// $this->on( 'voxel_ajax_nopriv_tracking.get_chart_data', '@get_chart_data' );
	}

	protected function get_chart_data() {
		try {
			$source = $_GET['source'] ?? null;
			$timeframe = \Voxel\from_list( $_GET['timeframe'] ?? null, [ '24h', '7d', '30d', '12m' ], '7d' );
			$view_type = ( $_GET['view_type'] ?? null ) === 'unique_views' ? 'unique_views' : 'views';

			if ( $source === 'post' ) {
				$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
				if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 110 );
				}

				\Voxel\verify_nonce( $_GET['_wpnonce'] ?? null, 'ts-visits-chart--p'.$post->get_id() );
				if ( ! ( $post->post_type && $post->post_type->is_tracking_enabled() ) ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 111 );
				}

				$post->stats->get_views('1d'); // refresh cache if needed
				$last_updated = $post->stats->get_views_last_updated_time(); // get last updated time

				// check if chart cache is synced with views cache, if so retrieve results directly
				$cache = $post->stats->get_chart_cache()[ $view_type ][ $timeframe ] ?? [];
				if ( strtotime( $cache['t'] ?? '' ) === $last_updated && ! empty( $cache['data'] ) ) {
					// \Voxel\log('cache');
					$data = $this->calculate_chart( $timeframe, [
						'post_id' => $post->get_id(),
						'count' => $view_type,
						'cache_views' => $cache['data'],
						'cache_date' => new \DateTime( date( 'Y-m-d H:i:s', strtotime( $cache['t'] ) ) ),
					] );
				} else {
					// \Voxel\log( 'db' );
					$data = $this->calculate_chart( $timeframe, [
						'post_id' => $post->get_id(),
						'count' => $view_type,
					] );

					$post->stats->set_chart_cache( $view_type, $timeframe, $data['views'], date( 'Y-m-d H:i:s', $last_updated ) );
				}

				return wp_send_json( [
					'success' => true,
					'data' => $data,
				] );
			} elseif ( $source === 'user' ) {
				\Voxel\verify_nonce( $_GET['_wpnonce'] ?? null, 'ts-visits-chart--u'.get_current_user_id() );
				$user = \Voxel\current_user();
				if ( ! $user ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 120 );
				}

				$user->stats->get_views('1d'); // refresh cache if needed
				$last_updated = $user->stats->get_views_last_updated_time(); // get last updated time

				// check if chart cache is synced with views cache, if so retrieve results directly
				$cache = $user->stats->get_chart_cache()[ $view_type ][ $timeframe ] ?? [];
				if ( strtotime( $cache['t'] ?? '' ) === $last_updated && ! empty( $cache['data'] ) ) {
					// \Voxel\log('cache user');
					$data = $this->calculate_chart( $timeframe, [
						'user_id' => $user->get_id(),
						'count' => $view_type,
						'cache_views' => $cache['data'],
						'cache_date' => new \DateTime( date( 'Y-m-d H:i:s', strtotime( $cache['t'] ) ) ),
					] );
				} else {
					// \Voxel\log( 'db user' );
					$data = $this->calculate_chart( $timeframe, [
						'user_id' => $user->get_id(),
						'count' => $view_type,
					] );

					$user->stats->set_chart_cache( $view_type, $timeframe, $data['views'], date( 'Y-m-d H:i:s', $last_updated ) );
				}

				return wp_send_json( [
					'success' => true,
					'data' => $data,
				] );
			} elseif ( $source === 'site' ) {
				\Voxel\verify_nonce( $_GET['_wpnonce'] ?? null, 'ts-visits-chart--site' );

				\Voxel\Stats\get_sitewide_views('1d'); // refresh cache if needed
				$last_updated = \Voxel\Stats\get_sitewide_views_last_updated_time(); // get last updated time

				// check if chart cache is synced with views cache, if so retrieve results directly
				$cache = \Voxel\Stats\get_sitewide_chart_cache()[ $view_type ][ $timeframe ] ?? [];
				if ( strtotime( $cache['t'] ?? '' ) === $last_updated && ! empty( $cache['data'] ) ) {
					// \Voxel\log('cache site');
					$data = $this->calculate_chart( $timeframe, [
						'count' => $view_type,
						'cache_views' => $cache['data'],
						'cache_date' => new \DateTime( date( 'Y-m-d H:i:s', strtotime( $cache['t'] ) ) ),
					] );
				} else {
					// \Voxel\log( 'db site' );
					$data = $this->calculate_chart( $timeframe, [
						'count' => $view_type,
					] );

					\Voxel\Stats\set_sitewide_chart_cache( $view_type, $timeframe, $data['views'], date( 'Y-m-d H:i:s', $last_updated ) );
				}

				$data = $this->calculate_chart( $timeframe, [
					'count' => $view_type,
				] );

				return wp_send_json( [
					'success' => true,
					'data' => $data,
				] );
			}

			throw new \Exception( __( 'Invalid request.', 'voxel' ), 101 );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	public function calculate_chart( $timeframe, array $args = [] ) {
		$args = array_merge( [
			'count' => 'views', // views|unique_views
			'post_id' => null,
			'user_id' => null,
			'cache_views' => null,
			'cache_date' => null,
		], $args );

		if ( ! in_array( $timeframe, [ '24h', '7d', '30d', '12m' ], true ) ) {
			throw new \Exception( __( 'Invalid request.', 'voxel' ), 141 );
		}

		if ( $args['cache_date'] !== null ) {
			$end_date = $args['cache_date'];
		} else {
			$end_date = \DateTime::createFromImmutable( \Voxel\utc() );
		}

		if ( $timeframe === '24h' ) {
			$start_date = new \DateTime( date( 'Y-m-d H:00:00', strtotime( '-23 hours', $end_date->getTimestamp() ) ) );
			$period = 'hourly';
		}  elseif ( $timeframe === '7d' ) {
			$start_date = new \DateTime( date( 'Y-m-d 00:00:00', strtotime( '-6 days', $end_date->getTimestamp() ) ) );
			$period = 'daily';
		} elseif ( $timeframe === '30d' ) {
			$start_date = new \DateTime( date( 'Y-m-d 00:00:00', strtotime( '-29 days', $end_date->getTimestamp() ) ) );
			$period = 'daily';
		} else /* $timeframe === '12m' */ {
			$start_date = new \DateTime( date( 'Y-m-01 00:00:00', strtotime( '-11 months', $end_date->getTimestamp() ) ) );
			$period = 'monthly';
		}

		$original_start_date = clone $start_date;

		if ( $end_date <= $start_date ) {
			throw new \Exception( __( 'Invalid request.', 'voxel' ), 140 );
		}

		if ( $args['cache_views'] !== null ) {
			$views = $args['cache_views'];
		} else {
			$views = $this->calculate_views_in_date_range( $start_date, $end_date, [
				'post_id' => $args['post_id'],
				'user_id' => $args['user_id'],
				'period' => $period,
			] );
		}

		$min = 0;
		$column = $args['count'] === 'unique_views' ? 'unique_views' : 'views';
		$max = max( ! empty( $views ) ? max( array_column( $views, $column ) ) : 0, 1 );
		$steps = $this->get_steps_from_max_views( $max );
		$tz_offset = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

		$items = [];
		while ( $start_date < $end_date ) {
			if ( $timeframe === '24h' ) {
				$key = $start_date->format( 'Y-m-d H:i' );
				$label = date_i18n(
					_x( 'H:00', 'visits chart time format (daily)', 'voxel' ),
					$start_date->getTimestamp() + $tz_offset
				);
			} elseif ( $timeframe === '7d' ) {
				$key = $start_date->format( 'Y-m-d' );
				$label = date_i18n(
					_x( 'l', 'visits chart time format (weekly)', 'voxel' ),
					$start_date->getTimestamp()
				);
			} elseif ( $timeframe === '30d' ) {
				$key = $start_date->format( 'Y-m-d' );
				$label = date_i18n(
					_x( 'M jS', 'visits chart time format (monthly)', 'voxel' ),
					$start_date->getTimestamp()
				);
			} else {
				$key = $start_date->format( 'Y-m' );
				$label = date_i18n(
					_x( 'M', 'visits chart time format (yearly)', 'voxel' ),
					$start_date->getTimestamp()
				);
			}

			if ( $start_date > $end_date ) {
				break;
			}

			if ( $timeframe === '24h' ) {
				$next_key = date( 'Y-m-d H:i', $start_date->getTimestamp() + HOUR_IN_SECONDS );
				if ( isset( $views[ $next_key ] ) ) {
					if ( ! isset( $views[ $key ] ) ) {
						$views[ $key ] = [
							'views' => 0,
							'unique_views' => 0,
						];
					}

					$views[ $key ]['views'] += $views[ $next_key ]['views'];
					$views[ $key ]['unique_views'] += $views[ $next_key ]['unique_views'];
					unset( $views[ $next_key ] );
				}
			}

			if ( isset( $views[ $key ] ) ) {
				$items[] = [
					'label' => $label,
					'percent' => round( ( $views[ $key ][ $column ] / $max ) * 100, 3 ),
					'count' => html_entity_decode( number_format_i18n( $views[ $key ]['views'] ) ),
					'unique_count' => html_entity_decode( number_format_i18n( $views[ $key ]['unique_views'] ) ),
				];
			} else {
				$items[] = [
					'label' => $label,
					'percent' => 0,
					'count' => 0,
				];
			}

			if ( $timeframe === '24h' ) {
				$start_date->modify( '+2 hours' );
			} elseif ( $timeframe === '7d' ) {
				$start_date->modify( '+1 day' );
			} elseif ( $timeframe === '30d' ) {
				$start_date->modify( '+1 day' );
			} else {
				$start_date->modify( '+1 month' );
			}
		}

		$meta = [
			'label' => sprintf(
				'%s - %s',
				\Voxel\date_format( $original_start_date->getTimestamp() ),
				\Voxel\date_format( $end_date->getTimestamp() )
			),
			'has_activity' => ! empty( $items ) && max( array_column( $items, 'count' ) ) > 0,
		];

		return compact( 'steps', 'items', 'meta', 'views' );
	}

	protected function calculate_views_in_date_range( \DateTime $start_date, \DateTime $end_date, array $args ) {
		$args = array_merge( [
			'period' => 'daily', // hourly|daily|monthly
			'post_id' => null,
			'user_id' => null,
		], $args );

		global $wpdb;

		$start_stamp = $start_date->getTimestamp();
		$end_stamp = $end_date->getTimestamp();
		if ( ! ( $start_stamp && $end_stamp && $end_stamp >= $start_stamp ) ) {
			return [];
		}

		$start_range = esc_sql( date( 'Y-m-d 00:00:00', $start_stamp ) );
		$end_range = esc_sql( date( 'Y-m-d 23:59:59', $end_stamp ) );

		$where_post = '';
		if ( is_numeric( $args['post_id'] ) ) {
			$where_post = $wpdb->prepare( " AND v.post_id = %d", $args['post_id'] );
		}

		$posts_join = '';
		$where_author = '';
		if ( is_numeric( $args['user_id'] ) ) {
			$posts_join = " LEFT JOIN {$wpdb->posts} AS p on v.post_id = p.ID ";
			$where_author = $wpdb->prepare( " AND p.post_author = %d", $args['user_id'] );
		}

		if ( $args['period'] === 'hourly' ) {
			$select_period = "DATE_FORMAT( v.created_at, '%Y-%m-%d %H:00' )";
		} elseif ( $args['period'] === 'daily' ) {
			$select_period = "DATE_FORMAT( v.created_at, '%Y-%m-%d' )";
		} else {
			$select_period = "DATE_FORMAT( v.created_at, '%Y-%m' )";
		}

		$results = $wpdb->get_results( <<<SQL
			SELECT
				COUNT(*) AS `views`,
				COUNT(DISTINCT `unique_id`) AS `unique_views`,
				{$select_period} AS `period`
			FROM {$wpdb->prefix}voxel_visits AS v
			{$posts_join}
			WHERE v.created_at >= '{$start_range}'
				AND v.created_at <= '{$end_range}'
				{$where_post}
				{$where_author}
			GROUP BY `period`
			ORDER BY `period` ASC
		SQL );

		$items = [];
		foreach ( $results as $period ) {
			$items[ $period->period ] = [
				'views' => (int) $period->views,
				'unique_views' => (int) $period->unique_views,
			];
		}

		return $items;
	}

	protected function get_steps_from_max_views( $max ) {
		$steps = [ $max, $max * 0.8, $max * 0.6, $max * 0.4, $max * 0.2, 0 ];
		$steps = array_map( function( $step ) {
			// $step = round( $step, -1 );
			return html_entity_decode( number_format_i18n( $step ) );
		}, $steps );

		return array_unique( $steps );
	}
}
