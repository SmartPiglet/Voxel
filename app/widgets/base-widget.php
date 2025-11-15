<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Base_Widget extends \Elementor\Widget_Base {

	protected function apply_controls( $option_group ) {
		$controls = $option_group.'::controls';
		$controls( $this );
	}

	/**
	 * Workaround to getting the widget's template id.
	 *
	 * @link https://github.com/elementor/elementor/issues/7495#issuecomment-1019656235
	 * @since 1.0
	 */
	protected function _get_template_id() {
		if ( is_admin() ) {
			$current_doc = \Elementor\Plugin::$instance->documents->get_current();
			return $current_doc ? $current_doc->get_id() : get_the_ID();
		}

		ini_set( 'zend.exception_ignore_args', 0 );
		$e = new \Exception();
		$trace = $e->getTrace();
		foreach ( $trace as $row ) {
			if ( $row['function'] === 'get_builder_content' && isset( $row['args'][0] ) ) {
				$template_id = $row['args'][0];
				break;
			}
		}

		return $template_id ?? get_the_ID();
	}
}
