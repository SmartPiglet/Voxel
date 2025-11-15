<?php

namespace {
	if ( ! defined('ABSPATH') ) {
		exit;
	}

	if ( file_exists( locate_template('vendor/autoload.php') ) ) {
		require_once locate_template('vendor/autoload.php');
	}

	if ( ! function_exists('dump') ) {
		function dump( $expression ) {
			echo '<pre>';
			foreach ( func_get_args() as $expression ) {
				var_dump( $expression );
				echo '<hr>';
			}
			echo '</pre>';
		}
	}

	if ( ! function_exists('dd') ) {
		function dd() {
			foreach ( func_get_args() as $expression ) {
				dump( $expression );
			}
			die;
		}
	}

	if ( ! function_exists('dump_sql') ) {
		function dump_sql( $sql ) {
			if ( ! class_exists( '\Doctrine\SqlFormatter\SqlFormatter' ) ) {
				dump( $sql );
				return;
			}

			static $formatter;
			if ( is_null( $formatter ) ) {
				$formatter = new \Doctrine\SqlFormatter\SqlFormatter(
					new \Doctrine\SqlFormatter\HtmlHighlighter( [ 'pre' => 'sql' ] )
				);
			}

			echo $formatter->format( $sql );
		}
	}

	if ( ! function_exists('dd_sql') ) {
		function dd_sql( $sql ) {
			dump_sql($sql);
			die;
		}
	}
}

namespace Voxel {
	if ( ! defined('ABSPATH') ) {
		exit;
	}

	function log() {
		if ( ! ( \Voxel\is_debug_mode() && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) ) {
			return;
		}

		$messages = [];
		foreach ( func_get_args() as $arg ) {
			if ( is_a( $arg, \Exception::class ) ) {
				$messages[] = sprintf( 'Exception (code: %d) %s%s', $arg->getCode(), $arg->getMessage(), "\n".$arg->getTraceAsString() );
			} elseif ( is_string( $arg ) || is_numeric( $arg ) ) {
				$messages[] = $arg;
			} else {
				$messages[] = wp_json_encode( $arg );
			}
		}

		if ( ! empty( $messages ) ) {
			error_log( join( "\r\n\t| ", $messages ) );
		}
	}

	function measure_start( $key ) {
		if ( ! \Voxel\is_qm_active() ) {
			return;
		}

		global $_vx_measures;
		if ( ! is_array( $_vx_measures ) ) {
			$_vx_measures = [];
		}

		if ( ! isset( $_vx_measures[ $key ] ) ) {
			$_vx_measures[ $key ] = [
				'total_time' => 0,
				'start_time' => false,
			];
		}

		$_vx_measures[ $key ]['start_time'] = microtime( true );

	}

	function measure_end( $key ) {
		if ( ! \Voxel\is_qm_active() ) {
			return;
		}

		global $_vx_measures;

		if ( ! $_vx_measures[ $key ]['start_time'] ) {
			return;
		}

		$_vx_measures[ $key ]['total_time'] += microtime( true ) - $_vx_measures[ $key ]['start_time'];
		$_vx_measures[ $key ]['start_time'] = false;
	}
	if ( \Voxel\is_qm_active() ) {
		add_action( 'voxel/body-end', function() {
			global $_vx_measures;

			if ( ! empty( $_vx_measures ) ) {
				$measures = [];
				foreach ( $_vx_measures as $key => $measure ) {
					$measures[] = [
						'key' => $key,
						'time' => round( $measure['total_time'] * 1000, 1 )
					];
				}

				usort( $measures, function( $a, $b ) {
					return $b['time'] - $a['time'];
				} );

				$output = join( "\n", array_map( function( $item ) {
					return sprintf( '%s: %sms', $item['key'], $item['time'] );
				}, $measures ) );
				do_action( 'qm/info', "Time measurements:\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n".$output );
			}
		} );
	}
}
