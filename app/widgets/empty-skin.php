<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Empty_Skin extends \Elementor\Skin_Base {

	public function get_title() {
		return 'Voxel Empty Skin';
	}

	public function get_id() {
		return 'voxel-empty-skin';
	}

	public function render() {
		return '';
	}

}
