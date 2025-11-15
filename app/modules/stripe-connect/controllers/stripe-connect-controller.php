<?php

namespace Voxel\Modules\Stripe_Connect\Controllers;

use \Voxel\Modules\Stripe_Connect as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Connect_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return Module\is_marketplace_active();
	}

	protected function dependencies() {
		new Frontend\Frontend_Controller;
	}

	protected function hooks() {
		$this->on( 'elementor/widgets/register', '@register_widgets', 1100 );
	}

	protected function register_widgets() {
		$manager = \Elementor\Plugin::instance()->widgets_manager;
		$manager->register( new Module\Widgets\Stripe_Account_Widget );
	}

}
