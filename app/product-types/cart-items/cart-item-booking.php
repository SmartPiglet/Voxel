<?php

namespace Voxel\Product_Types\Cart_Items;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Item_Booking extends Cart_Item {

	public function get_type(): string {
		return 'booking';
	}

	public function get_subtitle(): string {
		$subtitle = $this->get_form_field('form-booking')->get_selection_summary( $this->value );

		if ( $addons = $this->get_form_field('form-addons') ) {
			$subtitle .= ', '. $addons->get_selection_summary( $this->value );
		}

		return $subtitle;
	}

	public function get_pricing_summary(): array {
		$config = $this->product_field->get_value();
		$value = $this->get_value();

		$summary = [];

		if ( $base_price = $this->product_field->get_product_field('base-price') ) {
			$booking = $this->get_form_field('form-booking');
			$amount = $booking->get_base_price_amount( $value );
			if ( $amount !== null ) {
				$summary[] = [
					'key' => 'base_price',
					'amount' => $amount,
				];
			}
		}

		if ( $addons = $this->get_form_field('form-addons') ) {
			$summary[] = $addons->get_pricing_summary( $this->value );
		}

		$total_amount = 0;
		foreach ( $summary as $summary_item ) {
			$total_amount += $summary_item['amount'];
		}

		return [
			'summary' => $summary,
			'total_amount' => $total_amount,
		];
	}

	public function get_order_item_config() {
		$config = parent::get_order_item_config();

		if ( $this->product_type->config('modules.booking.type') === 'timeslots' ) {
			$config['booking'] = [
				'type' => 'timeslots',
				'date' => $this->value['booking']['date'],
				'slot' => [
					'from' => $this->value['booking']['slot']['from'],
					'to' => $this->value['booking']['slot']['to'],
				],
			];
		} else /* type === days */ {
			if ( $this->product_field->get_value()['booking']['booking_mode'] === 'single_day' ) {
				$config['booking'] = [
					'type' => 'single_day',
					'date' => $this->value['booking']['date'],
				];
			} else /* booking_mode === date_range */ {
				$config['booking'] = [
					'type' => 'date_range',
					'count_mode' => $this->product_type->config('modules.booking.date_ranges.count_mode'),
					'start_date' => $this->value['booking']['start_date'],
					'end_date' => $this->value['booking']['end_date'],
				];
			}
		}

		return $config;
	}

}
