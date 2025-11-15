<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Addons_Field extends Base_Field {

	protected $props = [
		'key' => 'form-addons',
		'label' => 'Addons',
	];

	public function get_conditions(): array {
		return [
			'settings.product_mode' => [
				'compare' => 'in_array',
				'value' => [ 'regular', 'booking' ],
			],
			'modules.addons.enabled' => true,
		];
	}

	public function get_active_addons() {
		$addons = $this->product_field->get_product_field('addons')->get_addons();

		$addons = array_filter( $addons, function( $addon ) {
			return $addon->is_active();
		} );

		return $addons;
	}

	public function set_schema( Data_Object $schema ): void {
		$addons_schema = Schema::Object( [] )->default([]);
		foreach ( $this->get_active_addons() as $addon ) {
			$addons_schema->set_prop( $addon->get_key(), $addon->get_product_form_schema() );
		}

		$schema->set_prop( 'addons', $addons_schema );
	}

	public function validate( $value ) {
		$active_addons = $this->get_active_addons();
		foreach ( $value['addons'] as $addon_key => $addon_value ) {
			if ( ! isset( $active_addons[ $addon_key ] ) ) {
				throw new \Exception( _x( 'Addon is no longer available', 'product form', 'voxel' ) );
			}

			$active_addons[ $addon_key ]->validate_in_cart_item( $addon_value );
		}
	}

	public function get_custom_price_for_addon( $addon, $value ) {
		$product_mode = $this->product_type->get_product_mode();
		$config = $this->product_field->get_value();

		if ( $product_mode === 'booking' ) {
			$booking = $this->product_field->get_form_field( 'form-booking' );
			if ( ! $booking ) {
				return null;
			}

			$booking_type = $this->product_type->config('modules.booking.type');
			if ( ! (
				$booking_type === 'timeslots' || ( $booking_type === 'days' && $config['booking']['booking_mode'] === 'single_day' )
			) ) {
				return null;
			}

			$date = new \DateTime( $value['booking']['date'], new \DateTimeZone('UTC') );
			return $this->product_field->get_custom_price_for_date( $date );
		} elseif ( $product_mode === 'regular' ) {
			$date = ( new \DateTime( 'now', $this->product_field->get_post()->get_timezone() ) );
			return $this->product_field->get_custom_price_for_date( $date );
		} else {
			return null;
		}
	}

	public function get_pricing_summary( $value ) {
		$summary = [
			'key' => 'addons',
			'summary' => [],
			'amount' => 0,
		];

		$booking = $this->product_field->get_form_field( 'form-booking' );

		foreach ( $this->get_active_addons() as $addon ) {
			if ( $booking ) {
				$addon->set_repeat_config( $booking->get_repeat_config( $addon, $value ) );
			}

			$addon->set_custom_price( $this->get_custom_price_for_addon( $addon, $value ) );

			$addon_summary = $addon->get_pricing_summary( $value['addons'][ $addon->get_key() ] );
			if ( $addon_summary !== null ) {
				$summary['summary'][] = $addon_summary;
				$summary['amount'] += $addon_summary['amount'];
			}
		}

		return $summary;
	}

	public function get_selection_summary( $value ) {
		$summary = [];
		$pricing_summary = $this->get_pricing_summary( $value );
		foreach ( $pricing_summary['summary'] as $item ) {
			if ( $item['type'] === 'switcher' ) {
				$summary[] = $item['label'];
			} elseif ( $item['type'] === 'numeric' ) {
				$summary[] = sprintf( '%s × %d', $item['label'], $item['quantity'] );
			} elseif ( $item['type'] === 'select' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], $item['selected'] );
			} elseif ( $item['type'] === 'multiselect' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], join( ', ', $item['selected'] ) );
			} elseif ( $item['type'] === 'custom-select' ) {
				$summary[] = $item['quantity'] !== null
					? sprintf( '%s: %s × %d', $item['label'], $item['selected'], $item['quantity'] )
					: sprintf( '%s: %s', $item['label'], $item['selected'] );
			} elseif ( $item['type'] === 'custom-multiselect' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], join( ', ', array_map( function( $choice ) {
					return $choice['quantity'] !== null
						? sprintf( '%s × %d', $choice['label'], $choice['quantity'] )
						: $choice['label'];
				}, $item['summary'] ) ) );
			}
		}

		return join( ', ', $summary );
	}

	public function frontend_props(): array {
		return [
			'addons' => array_map( function( $addon ) {
				return $addon->get_product_form_frontend_config();
			}, $this->get_active_addons() ),
			'l10n' => [
				'amount_nights' => _x( '@count nights', 'product form', 'voxel' ),
				'amount_days' => _x( '@count days', 'product form', 'voxel' ),
			],
		];
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/product-form/form-addons.php' );
		// $templates[] = locate_template( 'templates/widgets/product-form/form-addons/_external-choice.php' );

		foreach ( $this->get_active_addons() as $addon ) {
			$templates[] = locate_template( sprintf( 'templates/widgets/product-form/form-addons/%s.php', $addon->get_type() ) );
		}

		return $templates;
	}
}
