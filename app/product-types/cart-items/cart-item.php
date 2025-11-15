<?php

namespace Voxel\Product_Types\Cart_Items;

use Voxel\Utils\Config_Schema\Schema;
use Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Cart_Item {

	protected
		$post,
		$product_field,
		$product_type,
		$schema,
		$value,
		$key;

	abstract public function get_type(): string;

	abstract public function get_pricing_summary(): array;

	public static function create( array $raw_config, ?string $key = null ) {
		$post = \Voxel\Post::get( $raw_config['product']['post_id'] ?? null );
		if ( ! $post ) {
			throw new \Exception( __( 'Product not available', 'voxel' ), 10 );
		}

		$product_field = $post->get_field( $raw_config['product']['field_key'] ?? null  );
		if ( ! ( $product_field && $product_field->get_type() === 'product' ) ) {
			throw new \Exception( __( 'Product not available', 'voxel' ), 11 );
		}

		$product_type = $product_field->get_product_type();
		if ( ! $product_type ) {
			throw new \Exception( __( 'Product not available', 'voxel' ), 12 );
		}

		$product_mode = $product_type->get_product_mode();

		if ( $product_mode === 'regular' ) {
			return new Cart_Item_Regular( $raw_config, $key );
		} elseif ( $product_mode === 'variable' ) {
			return new Cart_Item_Variable( $raw_config, $key );
		} elseif ( $product_mode === 'booking' ) {
			return new Cart_Item_Booking( $raw_config, $key );
		} else {
			throw new \Exception( __( 'Product not available', 'voxel' ), 13 );
		}
	}

	protected function __construct( array $raw_config, ?string $key = null ) {
		$post = \Voxel\Post::get( $raw_config['product']['post_id'] ?? null );
		$product_field = $post->get_field( $raw_config['product']['field_key'] ?? null  );
		$product_type = $product_field->get_product_type();

		$schema = $product_field->get_product_form_schema();
		$schema->set_value( $raw_config );

		$this->post = $post;
		$this->product_field = $product_field;
		$this->product_type = $product_type;
		$this->schema = $schema;
		$this->value = $schema->export();
		$this->key = $key ?? strtolower( \Voxel\random_string(8) );
	}

	public function get_post(): \Voxel\Post {
		return $this->post;
	}

	public function get_product(): \Voxel\Post {
		return $this->get_post();
	}

	public function get_vendor(): ?\Voxel\User {
		$vendor = $this->post->get_author();
		if ( ! $vendor ) {
			return null;
		}

		if ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
			return null;
		}

		if ( ! $this->product_type->supports_marketplace() ) {
			return null;
		}

		return $vendor;
	}

	public function get_product_field() {
		return $this->product_field;
	}

	public function get_product_field_key() {
		return $this->product_field->get_key();
	}

	public function get_product_type() {
		return $this->product_type;
	}

	public function get_product_type_key() {
		return $this->product_type->get_key();
	}

	public function get_product_mode() {
		return $this->product_type->get_product_mode();
	}

	public function get_currency() {
		return $this->product_field->get_currency();
	}

	public function get_payment_method(): ?string {
		$payment_mode = $this->product_type->config( 'settings.payments.mode' );
		$payment_service = Payment_Service::get_active();

		if ( $payment_mode === 'payment' ) {
			return $payment_service ? $payment_service->get_payment_handler() : null;
		} elseif ( $payment_mode === 'subscription' ) {
			return $payment_service ? $payment_service->get_subscription_handler() : null;
		} elseif ( $payment_mode === 'offline' ) {
			return 'offline_payment';
		} else {
			return null;
		}
	}

	public function get_value() {
		return $this->value;
	}

	public function get_key() {
		return $this->key;
	}

	public function get_value_for_storage() {
		return Schema::optimize_for_storage( $this->value );
	}

	public function validate() {
		$this->product_field->check_product_form_validity();

		foreach ( $this->product_field->get_form_fields() as $form_field ) {
			$form_field->validate( $this->value );
		}

		do_action( 'voxel/product_types/cart_item/validate', $this );
	}

	public function get_form_field( $field_key ) {
		return $this->product_field->get_form_field( $field_key );
	}

	public function get_title(): string {
		$title = $this->post->get_display_name();
		if ( empty( $title ) ) {
			$title = sprintf( 'Product #%d', $this->post->get_id() );
		}

		return apply_filters( 'voxel/product_types/cart_item/get_title', $title, $this );
	}

	public function get_subtitle(): string {
		return '';
	}

	public function get_link(): ?string {
		return apply_filters( 'voxel/product_types/cart_item/get_link', $this->post->get_link(), $this );
	}

	public function get_image_id(): ?int {
		if ( $featured_image = get_post_thumbnail_id( $this->value['product']['post_id'] ) ) {
			$image = wp_get_attachment_image( $featured_image );

			if ( ! empty( $image ) ) {
				return (int) $featured_image;
			}
		}

		$image_id = $this->post->get_avatar_id();
		if ( is_numeric( $image_id ) ) {
			return (int) $image_id;
		}

		$gallery = $this->post->get_field('gallery');
		if ( $gallery && $gallery->get_type() === 'image' ) {
			$image_ids = (array) $gallery->get_value();
			$image_id = array_shift( $image_ids );
			if ( is_numeric( $image_id ) ) {
				return (int) $image_id;
			}
		}

		return null;
	}

	public function get_image_markup(): ?string {
		return wp_get_attachment_image( $this->get_image_id(), 'thumbnail', false, [
			'class' => 'ts-status-avatar',
		] );
	}

	public function get_frontend_config(): array {
		$config = [
			'key' => $this->get_key(),
			'title' => $this->get_title(),
			'subtitle' => $this->get_subtitle(),
			'logo' => $this->get_image_markup(),
			'link' => $this->get_link(),
			'pricing' => $this->get_pricing_summary(),
			'currency' => $this->get_currency(),
			'stock_id' => $this->get_stock_id(),
			'product_mode' => $this->get_product_mode(),
			'quantity' => [
				'enabled' => false,
			],
			'shipping' => [
				'is_shippable' => $this->is_shippable(),
				'shipping_class' => $this->get_shipping_class_key(),
			],
			'value' => $this->get_value(),
			'vendor' => [
				'id' => null,
				'display_name' => _x( 'Platform', 'cart summary', 'voxel' ),
			],
			'metadata' => apply_filters( 'voxel/cart_summary/item/metadata', [], $this ),
			'components' => apply_filters( 'voxel/cart_summary/item/components', [], $this ),
		];

		if ( $vendor = $this->get_vendor() ) {
			$config['vendor']['id'] = $vendor->get_id();
			$config['vendor']['display_name'] = $vendor->get_display_name();

			if ( \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor' ) {
				$config['vendor']['shipping_zones'] = (object) array_map( function( $shipping_zone ) {
					return $shipping_zone->get_frontend_config();
				}, $vendor->get_vendor_shipping_zones() );
				$config['vendor']['shipping_countries'] = [];

				// determine countries with shipping support
				$countries = \Voxel\Modules\Stripe_Payments\Country_Codes::all();
				foreach ( $vendor->get_vendor_shipping_zones() as $shipping_zone ) {
					foreach ( $shipping_zone->get_supported_country_codes() as $country_code => $enabled ) {
						if ( isset( $countries[ $country_code ] ) ) {
							$config['vendor']['shipping_countries'][ $country_code ] = $countries[ $country_code ];
						}
					}
				}
				asort( $config['vendor']['shipping_countries'] );
				$config['vendor']['shipping_countries'] = (object) $config['vendor']['shipping_countries'];
			}
		}

		return apply_filters( 'voxel/cart_summary/item/frontend_config', $config, $this );
	}

	/**
	 * Used to identify cart items of the same type for grouping purposes.
	 *
	 * Regular products: Two items with the same product (post) ID, product field key,
	 * either stock disabled or stock enabled && not sold individually, and the exact
	 * same configuration of addons, will generate the same id.
	 *
	 * Variable products: Two items with the same product (post) ID, product field key,
	 * same variation selected, and either stock disabled or stock enabled && not sold
	 * individually, will generate the same id.
	 *
	 * Other product modes (booking, ...): Can't be added to cart, no id necessary.
	 *
	 * @since 1.4.0
	 */
	public function get_group_id() {
		if ( ! $this->product_type->config('modules.stock.enabled') ) {
			return null;
		}

		$value = $this->get_value();
		$config = $this->product_field->get_value();
		$product_mode = $this->get_product_mode();

		if ( $product_mode === 'regular' ) {
			if ( ! $config['stock']['enabled'] || ( $config['stock']['enabled'] && $config['stock']['sold_individually'] ) ) {
				return null;
			}

			$data = [
				'product' => $value['product'],
				'addons' => $value['addons'] ?? [],
			];

			return md5( wp_json_encode( $data ) );
		} elseif ( $product_mode === 'variable' ) {
			$variation_id = $value['variations']['variation_id'];
			$stock_config = $config['variations']['variations'][ $variation_id ]['config']['stock'];
			if ( ! $stock_config['enabled'] || ( $stock_config['enabled'] && $stock_config['sold_individually'] ) ) {
				return null;
			}

			$data = [
				'product' => $value['product'],
				'variation' => $variation_id,
			];

			return md5( wp_json_encode( $data ) );
		} else {
			return null;
		}
	}

	/**
	 * Used to identify cart items with the same stock source.
	 *
	 * Regular products: Two items with the same product (post) ID, product field key,
	 * and with stock enabled, will have the same stock ID.
	 *
	 * Variable products: Two items with the same product (post) ID, product field key,
	 * same variation selected, and stock enabled, will have the same stock ID.
	 *
	 * Other product modes: Always unique hash, can't be grouped, can't be added to cart.
	 *
	 * @since 1.4.0
	 */
	public function get_stock_id() {
		if ( ! $this->product_type->config('modules.stock.enabled') ) {
			return null;
		}

		$value = $this->get_value();
		$config = $this->product_field->get_value();
		$product_mode = $this->get_product_mode();

		if ( $product_mode === 'regular' && $config['stock']['enabled'] ) {
			$data = [
				'post_id' => $value['product']['post_id'],
				'field_key' => $value['product']['field_key'],
			];

			return md5( wp_json_encode( $data ) );
		} elseif ( $product_mode === 'variable' ) {
			$variation_id = $value['variations']['variation_id'];
			$stock_config = $config['variations']['variations'][ $variation_id ]['config']['stock'];
			if ( ! $stock_config['enabled'] ) {
				return null;
			}

			$data = [
				'post_id' => $value['product']['post_id'],
				'field_key' => $value['product']['field_key'],
				'variation_id' => $variation_id,
			];

			return md5( wp_json_encode( $data ) );
		} else {
			return null;
		}
	}

	public function is_shippable(): bool {
		return false;
	}

	public function get_shipping_class_key(): ?string {
		return null;
	}

	public function get_shipping_class(): ?\Voxel\Product_Types\Shipping\Shipping_Class {
		return \Voxel\Product_Types\Shipping\Shipping_Class::get( $this->get_shipping_class_key() );
	}

	public function get_data_inputs(): ?array {
		$form_field = $this->get_form_field( 'form-data-inputs' );
		if ( $form_field === null ) {
			return null;
		}

		return $form_field->prepare_data_inputs_for_storage( $this->get_value() );
	}

	public function get_order_item_config() {
		$details = [
			'type' => $this->get_type(),
			'product' => [
				'label' => $this->get_title(),
				'image_id' => $this->get_image_id(),
			],
			'currency' => $this->get_currency(),
			'summary' => $this->get_pricing_summary(),
			'data_inputs' => $this->get_data_inputs(),
		];

		if ( $this->is_shippable() ) {
			$details['shipping'] = [
				'enabled' => true,
			];

			if ( $shipping_class = $this->get_shipping_class() ) {
				$details['shipping']['shipping_class'] = [
					'key' => $shipping_class->get_key(),
					'label' => $shipping_class->get_label(),
				];
			}
		}

		if ( $vendor = $this->get_vendor() ) {
			$details['vendor'] = [
				'id' => $vendor->get_id(),
			];
		}

		if ( $this->get_product_mode() === 'regular' && $this->product_type->config( 'settings.payments.mode' ) === 'subscription' ) {
			$details['subscription'] = [
				'frequency' => $this->product_field->get_value()['subscription']['frequency'],
				'unit' => $this->product_field->get_value()['subscription']['unit'],
			];
		}

		return apply_filters( 'voxel/product_types/cart_item/details', $details, $this );
	}

	public function is_catalog_product( ?string $category = null ): bool {
		if ( $this->post->post_type->get_key() !== '_vx_catalog' ) {
			return false;
		}

		if ( $category !== null ) {
			$catalog_category = get_post_meta( $this->post->get_id(), '_vx_catalog_category', true );

			return $catalog_category === $category;
		}

		return true;
	}
}
