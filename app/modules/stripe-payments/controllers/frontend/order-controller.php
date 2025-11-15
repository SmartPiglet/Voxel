<?php

namespace Voxel\Modules\Stripe_Payments\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/order/customer_details', '@add_customer_details', 10, 2 );
		$this->filter( 'voxel/order/shipping_details', '@add_shipping_details', 10, 2 );
	}

	protected function add_customer_details( $details, $order ) {
		$payment_method = $order->get_payment_method();
		if ( ! $payment_method ) {
			return $details;
		}

		if ( ! in_array( $payment_method->get_type(), [ 'stripe_payment', 'stripe_subscription' ], true ) ) {
			return $details;
		}

		$data = (array) $order->get_details( 'checkout.session_details.customer_details', [] );

		if ( ! empty( $data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Customer name', 'order customer details', 'voxel' ),
				'content' => $data['name'],
			];
		}

		if ( ! empty( $data['email'] ) ) {
			$details[] = [
				'label' => _x( 'Email', 'order customer details', 'voxel' ),
				'content' => $data['email'],
			];
		}

		if ( ! empty( $data['address']['country'] ) ) {
			$country_code = $data['address']['country'];
			$country = \Voxel\Utils\Country_List::all()[ strtoupper( $country_code ) ] ?? null;

			$details[] = [
				'label' => _x( 'Country', 'order customer details', 'voxel' ),
				'content' => $country['name'] ?? $country_code,
			];
		}

		if ( ! empty( $data['address']['line1'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 1', 'order customer details', 'voxel' ),
				'content' => $data['address']['line1'],
			];
		}

		if ( ! empty( $data['address']['line2'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 2', 'order customer details', 'voxel' ),
				'content' => $data['address']['line2'],
			];
		}

		if ( ! empty( $data['address']['city'] ) ) {
			$details[] = [
				'label' => _x( 'City', 'order customer details', 'voxel' ),
				'content' => $data['address']['city'],
			];
		}

		if ( ! empty( $data['address']['postal_code'] ) ) {
			$details[] = [
				'label' => _x( 'Postal code', 'order customer details', 'voxel' ),
				'content' => $data['address']['postal_code'],
			];
		}

		if ( ! empty( $data['address']['state'] ) ) {
			$details[] = [
				'label' => _x( 'State', 'order customer details', 'voxel' ),
				'content' => $data['address']['state'],
			];
		}

		if ( ! empty( $data['phone'] ) ) {
			$details[] = [
				'label' => _x( 'Phone number', 'order customer details', 'voxel' ),
				'content' => $data['phone'],
			];
		}

		return $details;
	}

	protected function add_shipping_details( $details, $order ) {
		$payment_method = $order->get_payment_method();
		if ( ! $payment_method ) {
			return $details;
		}

		if ( ! in_array( $payment_method->get_type(), [ 'stripe_payment' ], true ) ) {
			return $details;
		}

		$data = (array) $order->get_details( 'checkout.session_details.shipping_details', [] );

		if ( ! empty( $data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Recipient name', 'order shipping details', 'voxel' ),
				'content' => $data['name'],
			];
		}

		if ( ! empty( $data['address']['country'] ) ) {
			$country_code = $data['address']['country'];
			$country = \Voxel\Utils\Country_List::all()[ strtoupper( $country_code ) ] ?? null;

			$details[] = [
				'label' => _x( 'Country', 'order shipping details', 'voxel' ),
				'content' => $country['name'] ?? $country_code,
			];
		}

		if ( ! empty( $data['address']['line1'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 1', 'order shipping details', 'voxel' ),
				'content' => $data['address']['line1'],
			];
		}

		if ( ! empty( $data['address']['line2'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 2', 'order shipping details', 'voxel' ),
				'content' => $data['address']['line2'],
			];
		}

		if ( ! empty( $data['address']['city'] ) ) {
			$details[] = [
				'label' => _x( 'City', 'order shipping details', 'voxel' ),
				'content' => $data['address']['city'],
			];
		}

		if ( ! empty( $data['address']['postal_code'] ) ) {
			$details[] = [
				'label' => _x( 'Postal code', 'order shipping details', 'voxel' ),
				'content' => $data['address']['postal_code'],
			];
		}

		if ( ! empty( $data['address']['state'] ) ) {
			$details[] = [
				'label' => _x( 'State', 'order shipping details', 'voxel' ),
				'content' => $data['address']['state'],
			];
		}

		return $details;
	}

}
