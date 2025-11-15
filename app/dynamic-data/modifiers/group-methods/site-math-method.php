<?php

namespace Voxel\Dynamic_Data\Modifiers\Group_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Site_Math_Method extends Base_Group_Method {

	public function get_key(): string {
		return 'math';
	}

	public function get_label(): string {
		return _x( 'Math expression', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Math expression', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function run( $group ) {
		$raw_expression = $this->args[0]['content'] ?? '';
		$expression = $this->get_arg(0);

		$debug_info = [
			sprintf('Math Expression: %s', $raw_expression),
			sprintf('- Parsed as: %s', $expression),
		];

		$start_time = microtime(true);

		try {
			$result = \Voxel\evaluate_math_expression( $expression );
			$debug_info[] = sprintf( '- Evaluated value: %s', $result );
		} catch ( \Exception $e ) {
			$result = '';
			$debug_info[] = sprintf( '- Evaluation error: %s', $e->getMessage() );
		}

		$evaluation_time = microtime(true) - $start_time;
		$debug_info[] = sprintf( '- Evaluation time: %sms', round( $evaluation_time * 1000, 1 ) );

		do_action( 'qm/debug', join( "\n", $debug_info ) );

		return $result;
	}
}
