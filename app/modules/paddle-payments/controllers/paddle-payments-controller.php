<?php

namespace Voxel\Modules\Paddle_Payments\Controllers;

use Voxel\Modules\Paddle_Payments as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Payments_Controller extends \Voxel\Controllers\Base_Controller {

	protected function dependencies() {
		new Backend\Paddle_Settings_Controller;
		new Frontend\Paddle_Frontend_Controller;
		new Frontend\Paddle_Webhooks_Controller;
	}

	protected function hooks() {
		$this->filter( 'voxel/product-types/payment-services', '@register_payment_service' );
		$this->filter( 'voxel/product-types/payment-methods', '@register_payment_methods' );
	}

	protected function register_payment_service( $payment_services ) {
		$payment_services['paddle'] = new Module\Paddle_Payment_Service;

		return $payment_services;
	}

	protected function register_payment_methods( $payment_methods ) {
		$payment_methods['paddle_payment'] = Module\Payment_Methods\Paddle_Payment::class;
		$payment_methods['paddle_subscription'] = Module\Payment_Methods\Paddle_Subscription::class;

		return $payment_methods;
	}

}
