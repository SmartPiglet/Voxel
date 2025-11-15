<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Raw_Model extends Base_Form_Model {

	protected $cb = null;

	public function __construct( $cb ) {
		$this->cb = $cb;
	}

	public function get_template() {
		ob_start();
		$this->template();
		return ob_get_clean();
	}

	protected function template() {
		($this->cb)();
	}
}
