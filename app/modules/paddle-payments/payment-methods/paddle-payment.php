<?php

namespace Voxel\Modules\Paddle_Payments\Payment_Methods;

use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Create\TransactionCreateItemWithPrice;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogProduct;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPriceWithProduct;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\Money;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\CurrencyCode;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxCategory;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxMode;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use \Voxel\Vendor\Paddle\SDK\Entities\Transaction;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\List\Includes;
use \Voxel\Vendor\Paddle\SDK\Resources\CustomerPortalSessions\Operations\CreateCustomerPortalSession;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Paddle_Payment extends \Voxel\Product_Types\Payment_Methods\Base_Payment_Method {

	public function get_type(): string  {
		return 'paddle_payment';
	}

	public function get_label(): string {
		return _x( 'Paddle payment', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		try {
			$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
			$customer = $this->order->get_customer();
			$paddle_customer = $customer->get_or_create_paddle_customer();

			// $address = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_latest_active_address( $paddle_customer->id );
			// $business = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_latest_active_business( $paddle_customer->id );

			$items = array_map( function( $li ) {
				return new TransactionCreateItemWithPrice(
					quantity: $li['quantity'],
					price: new TransactionNonCatalogPriceWithProduct(
						description: sprintf( 'Product #%d (%s)', $li['id'], $li['product']['label'] ),
						taxMode: TaxMode::from('account_setting'),
						unitPrice: new Money(
							$li['amount_in_cents'],
							CurrencyCode::from( strtoupper( $li['currency'] ) ),
						),
						product: new TransactionNonCatalogProduct(
							taxCategory: TaxCategory::from('standard'),
							name: $li['product']['label'],
							description: $li['product']['description'] ?: null,
							imageUrl: $li['product']['thumbnail_url'] ?: null,
						),
					),
				);
			}, $this->get_line_items() );

			$operation = new CreateTransaction(
				customerId: $paddle_customer->id,
				items: $items,
				currencyCode: CurrencyCode::from( strtoupper( $this->order->get_currency() ) ),
				customData: new CustomData( [
					'voxel:payment_for' => 'order',
					'voxel:order_id' => (string) $this->order->get_id(),
				] ),
				// addressId: $address->id ?? null,
				// businessId: $business->id ?? null,
			);

			$transaction = $paddle->transactions->create( $operation );

			$totals = $transaction->details->totals;
			$total_order_amount = (int) $totals->total;
			$currency = $totals->currencyCode->getValue();

			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
			$this->order->set_details( 'checkout.transaction_id', $transaction->id );
			$this->order->set_transaction_id( $transaction->id );

			$this->order->save();

			$url = $transaction->checkout->url;
			$url = add_query_arg( 'transaction_id', $transaction->id, $url );

			return [ 'success' => true, 'redirect_url' => $url ];
		} catch ( \Throwable $e ) {
			return [
				'success' => false,
				'message' => _x( 'Something went wrong', 'checkout', 'voxel' ),
				'debug' => [
					'type' => 'paddle_error',
					'code' => $e->getCode(),
					'message' => $e->getMessage(),
				],
			];
		}
	}

	public function transaction_updated( Transaction $transaction ) {
		$this->order->set_transaction_id( $transaction->id );

		// update status
		$transaction_status = $transaction->status->getValue();
		if ( in_array( $transaction_status, [ 'completed', 'paid' ], true ) ) {
			$this->order->set_status( \Voxel\ORDER_COMPLETED );
		} elseif ( $transaction_status === 'canceled' ) {
			$this->order->set_status( \Voxel\ORDER_CANCELED );
		} else {
			//
		}

		if ( $adjustments = $transaction->adjustments ) {
			foreach ( $adjustments as $adjustment ) {
				if ( in_array( $adjustment->action->getValue(), [ 'refund', 'chargeback', 'chargeback_reverse' ], true ) ) {
					$this->order->set_status( \Voxel\ORDER_REFUNDED );
					break;
				}
			}
		}

		// save total
		$totals = $transaction->details->totals;
		$currency = $totals->currencyCode->getValue();

		$total_amount = (int) $totals->total;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$total_amount /= 100;
		}

		$this->order->set_details( 'pricing.total', $total_amount );

		// save subtotal
		$subtotal_amount = (int) $totals->subtotal;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$subtotal_amount /= 100;
		}

		$this->order->set_details( 'pricing.subtotal', $subtotal_amount );

		// save tax amount
		$tax_amount = (int) $totals->tax;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$tax_amount /= 100;
		}

		$this->order->set_details( 'pricing.tax', $tax_amount );

		// save discount amount
		$discount_amount = (int) $totals->discount;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$discount_amount /= 100;
		}

		$this->order->set_details( 'pricing.discount', $discount_amount );

		// save other details details
		$this->order->set_details( 'transaction', [
			'id' => $transaction->id,
			'status' => $transaction->status->getValue(),
			'customerId' => $transaction->customerId,
			'addressId' => $transaction->addressId,
			'businessId' => $transaction->businessId,
		] );

		if ( $address = $transaction->address ) {
			$this->order->set_details( 'address', [
				'firstLine' => $address->firstLine,
				'secondLine' => $address->secondLine,
				'city' => $address->city,
				'postalCode' => $address->postalCode,
				'region' => $address->region,
				'countryCode' => $address->countryCode,
			] );
		}

		if ( $business = $transaction->business ) {
			$this->order->set_details( 'business', [
				'name' => $business->name,
				'companyNumber' => $business->companyNumber,
				'taxIdentifier' => $business->taxIdentifier,
			] );
		}

		if ( $customer = $transaction->customer ) {
			$this->order->set_details( 'customer', [
				'name' => $customer->name,
				'email' => $customer->email,
				'marketingConsent' => $customer->marketingConsent,
			] );
		}

		$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
		$this->order->save();
	}

	public function should_sync(): bool {
		return ! $this->order->get_details( 'checkout.last_synced_at' );
	}

	public function sync(): void {
		$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
		try {
			$transaction = $paddle->transactions->get( (string) $this->order->get_transaction_id(), [
				Includes::from('address'),
				Includes::from('business'),
				Includes::from('customer'),
				Includes::from('adjustment'),
			] );
			$this->transaction_updated( $transaction );
		} catch ( \Exception $e ) {
			// \Voxel\log($e->getMessage());
		}
	}

	public function get_customer_details(): array {
		$details = [];

		$customer_data = (array) $this->order->get_details( 'customer', [] );

		if ( ! empty( $customer_data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Customer name', 'order customer details', 'voxel' ),
				'content' => $customer_data['name'],
			];
		}

		if ( ! empty( $customer_data['email'] ) ) {
			$details[] = [
				'label' => _x( 'Email', 'order customer details', 'voxel' ),
				'content' => $customer_data['email'],
			];
		}

		$address_data = (array) $this->order->get_details( 'address', [] );

		if ( ! empty( $address_data['countryCode'] ) ) {
			$country_code = $address_data['countryCode'];
			$country = \Voxel\Utils\Country_List::all()[ strtoupper( $country_code ) ] ?? null;

			$details[] = [
				'label' => _x( 'Country', 'order customer details', 'voxel' ),
				'content' => $country['name'] ?? $country_code,
			];
		}

		if ( ! empty( $address_data['firstLine'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 1', 'order customer details', 'voxel' ),
				'content' => $address_data['firstLine'],
			];
		}

		if ( ! empty( $address_data['secondLine'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 2', 'order customer details', 'voxel' ),
				'content' => $address_data['secondLine'],
			];
		}

		if ( ! empty( $address_data['city'] ) ) {
			$details[] = [
				'label' => _x( 'City', 'order customer details', 'voxel' ),
				'content' => $address_data['city'],
			];
		}

		if ( ! empty( $address_data['postalCode'] ) ) {
			$details[] = [
				'label' => _x( 'Postal code', 'order customer details', 'voxel' ),
				'content' => $address_data['postalCode'],
			];
		}

		if ( ! empty( $address_data['region'] ) ) {
			$details[] = [
				'label' => _x( 'State', 'order customer details', 'voxel' ),
				'content' => $address_data['region'],
			];
		}

		$business_data = (array) $this->order->get_details( 'business', [] );

		if ( ! empty( $business_data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Business name', 'order customer details', 'voxel' ),
				'content' => $business_data['name'],
			];
		}

		if ( ! empty( $business_data['companyNumber'] ) ) {
			$details[] = [
				'label' => _x( 'Company number', 'order customer details', 'voxel' ),
				'content' => $business_data['companyNumber'],
			];
		}

		if ( ! empty( $business_data['taxIdentifier'] ) ) {
			$details[] = [
				'label' => _x( 'VAT number', 'order customer details', 'voxel' ),
				'content' => $business_data['taxIdentifier'],
			];
		}

		return $details;
	}

	public function get_customer_actions(): array {
		$actions = [];

		$actions[] = [
			'action' => 'customer.access_portal',
			'label' => _x( 'Customer portal', 'order customer actions', 'voxel' ),
			'handler' => function() {
				$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
				$session = $paddle->customerPortalSessions->create(
					\Voxel\current_user()->get_paddle_customer_id(),
					new CreateCustomerPortalSession
				);

				return wp_send_json( [
					'success' => true,
					'redirect_to' => $session->urls->general->overview,
				] );
			},
		];

		return $actions;
	}
}
