<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Field extends Base_Post_Field {
	use Product_Field\Methods\Get_Minimum_Price;
	use Product_Field\Methods\Get_Prices_For_Index;
	use Product_Field\Exports;

	protected $props = [
		'type' => 'product',
		'label' => 'Product',
		'product-type' => '',
		'product-types' => [],
	];

	protected $supported_conditions = [
		'enabled' => [
			'label' => 'Is enabled',
			'supported_conditions' => [ 'switcher' ],
		],
		'product_type' => [
			'label' => 'Product type',
			'supported_conditions' => [ 'text' ],
		],
	];

	public function get_models(): array {
		$choices = [];
		foreach ( \Voxel\Product_Type::all() as $product_type ) {
			$choices[ $product_type->get_key() ] = $product_type->get_label();
		}

		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ] ),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ] ),
			'product-types' => [
				'type' => Form_Models\Checkboxes_Model::class,
				'label' => 'Product types',
				'classes' => 'x-col-12',
				'choices' => $choices,
			],
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
		];
	}

	public function get_schema(): Data_Object {
		$schema = Schema::Object( [
			'product_type' => Schema::Enum( (array) $this->props['product-types'] )->default( $this->props['product-types'][0] ?? null ),
			'enabled' => Schema::Bool(),
		] );

		foreach ( $this->get_product_fields() as $field ) {
			$field->set_schema( $schema );
		}

		return $schema;
	}

	public function sanitize( $value ) {
		if ( ! is_array( $value ) ) {
			return null;
		}

		if ( $this->set_product_type_by_key( $value['product_type'] ?? null ) === null ) {
			return null;
		}

		if ( $this->is_required() ) {
			$value['enabled'] = true;
		}

		$schema = $this->get_schema();
		$schema->set_value( $value );

		$data = $schema->export();

		foreach ( $this->get_product_fields() as $field ) {
			$data = $field->sanitize( $data, $value );
		}

		return $data;
	}

	public function validate( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			if ( $this->set_product_type_by_key( $value['product_type'] ?? null ) === null ) {
				throw new \Exception(
					\Voxel\replace_vars( _x( '@field_name: No product type selected', 'field validation', 'voxel' ), [
						'@field_name' => $this->get_label(),
					] )
				);
			}

			foreach ( $this->get_product_fields() as $field ) {
				$field->validate( $value );
			}
		}
	}

	public function update( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			if ( $this->set_product_type_by_key( $value['product_type'] ?? null ) === null ) {
				return;
			}

			foreach ( $this->get_product_fields() as $field ) {
				$value = $field->update( $value );
			}

			if ( is_array( $value ) ) {
				$value = Schema::optimize_for_storage( $value );
			}

			if ( empty( $value ) ) {
				delete_post_meta( $this->post->get_id(), $this->get_key() );
			} else {
				update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $value ) ) );
			}
		} else {
			$previous_value = $this->_get_raw_decoded_value();

			if ( empty( $previous_value ) ) {
				delete_post_meta( $this->post->get_id(), $this->get_key() );
			} else {
				$previous_value['enabled'] = false;
				update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $previous_value ) ) );
			}
		}
	}

	public function _direct_update( $value ): void {
		if ( is_array( $value ) ) {
			$value = Schema::optimize_for_storage( $value );
		}

		if ( empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( wp_json_encode( $value ) ) );
		}
	}

	protected $_raw_decoded_value;
	protected function _get_raw_decoded_value() {
		if ( $this->_raw_decoded_value === null ) {
			if ( $this->post ) {
				$this->_raw_decoded_value = (array) json_decode( get_post_meta( $this->post->get_id(), $this->get_key(), true ), true );
			} else {
				$this->_raw_decoded_value = [];
			}
		}

		return $this->_raw_decoded_value;
	}

	protected $_parsed_value;
	public function get_value_from_post() {
		if ( $this->_parsed_value === null ) {
			if ( $this->get_key() === 'voxel:promotion' ) {
				$this->_parsed_value = [
					'enabled' => $this->post->promotions->is_promotable_by_user( \Voxel\get_current_user() ),
					'product_type' => 'voxel:promotion',
				];
			} else {
				if ( ! $this->get_product_type() ) {
					return null;
				}

				$value = $this->_get_raw_decoded_value();
				if ( $this->is_required() ) {
					$value['enabled'] = true;
				}

				$schema = $this->get_schema();
				$schema->set_value( $value );

				$parsed_value = $schema->export();

				$this->_parsed_value = $parsed_value;
			}
		}

		return $this->_parsed_value;
	}

	public function _set_value( array $value ) {
		$this->_raw_decoded_value = $value;

		if ( ! $this->get_product_type() ) {
			return null;
		}

		if ( $this->is_required() ) {
			$value['enabled'] = true;
		}

		$schema = $this->get_schema();
		$schema->set_value( $value );

		$this->_parsed_value = $schema->export();
	}

	public function editing_value() {
		if ( $this->post ) {
			$value = $this->get_value_from_post();
		} else {
			$value = $this->get_schema()->export();
		}

		foreach ( $this->get_product_fields() as $field ) {
			$value = $field->editing_value( $value );
		}

		if ( $this->is_required() ) {
			$value['enabled'] = true;
		}

		return $value;
	}

	public function get_required_scripts(): array {
		$required_scripts = [ 'sortable', 'vue-draggable' ];

		foreach ( $this->get_product_fields() as $field ) {
			foreach ( $field->get_required_scripts() as $script_handle ) {
				$required_scripts[] = $script_handle;
			}
		}

		return $required_scripts;
	}

	protected function frontend_props() {
		$props = [
			'product_types' => [],
		];

		foreach ( $this->get_supported_product_types() as $product_type ) {
			$props['product_types'][ $product_type->get_key() ] = [
				'key' => $product_type->get_key(),
				'label' => $product_type->get_label(),
				'payment_mode' => $product_type->config('settings.payments.mode'),
				'fields' => [],
			];

			$schema = Schema::Object( [
				'product_type' => Schema::String()->default( $product_type->get_key() ),
				'enabled' => Schema::Bool(),
			] );

			foreach ( $product_type->repository->get_product_fields() as $field ) {
				$field = clone $field;
				$field->set_product_field( $this );
				$props['product_types'][ $product_type->get_key() ]['fields'][ $field->get_key() ] = $field->get_frontend_config();
				$field->set_schema( $schema );
			}

			$props['product_types'][ $product_type->get_key() ]['value'] = $schema->export();
		}

		return $props;
	}

	protected $supported_product_types;
	public function get_supported_product_types(): array {
		if ( $this->supported_product_types === null ) {
			$this->supported_product_types = [];
			foreach ( $this->props['product-types'] as $product_type_key ) {
				if ( $product_type = \Voxel\Product_Type::get( $product_type_key ) ) {
					$this->supported_product_types[ $product_type->get_key() ] = $product_type;
				}
			}
		}

		return $this->supported_product_types;
	}

	protected $product_type;
	public function get_product_type() {
		if ( $this->product_type === null ) {
			if ( $this->get_key() === 'voxel:promotion' ) {
				$product_type = \Voxel\Product_Type::get( 'voxel:promotion' );
			} else {
				if ( $this->post ) {
					$value = $this->_get_raw_decoded_value();
					$product_type = $this->set_product_type_by_key( $value['product_type'] ?? null );
					if ( $product_type === null ) {
						// migrate product_type from versions <= 1.3.5
						if ( ! empty( $this->props['product-type'] ) && in_array( $this->props['product-type'], (array) $this->props['product-types'], true ) ) {
							$product_type = \Voxel\Product_Type::get( $this->props['product-type'] );
						}

						if ( $product_type === null ) {
							$product_type = \Voxel\Product_Type::get( $this->props['product-types'][0] ?? null );
						}
					}
				} else {
					$product_type = \Voxel\Product_Type::get( $this->props['product-types'][0] ?? null );
				}
			}

			$this->product_type = $product_type;
		}

		return $this->product_type;
	}

	public function sanitize_in_editor( $props ) {
		if ( ! is_array( $props['product-types'] ) ) {
			$props['product-types'] = [];
		}

		$props['product-types'] = array_values( array_filter( $props['product-types'], function( $key ) {
			return !! \Voxel\Product_Type::get( $key );
		} ) );

		return $props;
	}

	public function set_product_type_by_key( $product_type_key ) {
		$product_type = \Voxel\Product_Type::get( (string) $product_type_key );
		if ( $product_type && in_array( $product_type->get_key(), (array) $this->props['product-types'], true ) ) {
			$this->product_type = $product_type;
			return $product_type;
		}

		return null;
	}

	protected $product_fields;
	public function get_product_fields() {
		if ( $this->product_fields === null ) {
			$product_type = $this->get_product_type();
			$this->product_fields = [];
			foreach ( $product_type->repository->get_product_fields() as $field ) {
				$field = clone $field;
				$field->set_product_field( $this );
				$this->product_fields[ $field->get_key() ] = $field;
			}
		}

		return $this->product_fields;
	}

	public function get_product_field( $field_key ) {
		$fields = $this->get_product_fields();
		return $fields[ $field_key ] ?? null;
	}

	protected $form_fields;
	public function get_form_fields() {
		if ( $this->form_fields === null ) {
			$product_type = $this->get_product_type();

			$this->form_fields = [];
			foreach ( $product_type->repository->get_form_fields() as $field ) {
				$field = clone $field;
				$field->set_product_field( $this );
				$this->form_fields[ $field->get_key() ] = $field;
			}
		}

		return $this->form_fields;
	}

	public function get_form_field( $field_key ) {
		$fields = $this->get_form_fields();
		return $fields[ $field_key ] ?? null;
	}

	public function get_product_form_schema(): Data_Object {
		$schema = Schema::Object( [
			'product' => Schema::Object( [
				'post_id' => Schema::Int(),
				'field_key' => Schema::String(),
			] ),
			'custom_data' => Schema::Keyed_List()->default([]),
		] )->default( [] );

		if ( $this->get_key() === 'voxel:promotion' ) {
			$schema->set_prop( 'promotion_package', Schema::String() );
		}

		foreach ( $this->get_form_fields() as $field ) {
			$field->set_schema( $schema );
		}

		return $schema;
	}

	public function get_product_form_props(): array {
		$value = $this->get_value();
		$props = [
			'fields' => array_map( function( $field ) {
				return $field->get_frontend_config();
			}, $this->get_form_fields() ),
			'base_price' => [
				'enabled' => false,
			],
			'cart' => [
				'enabled' => $this->can_be_added_to_cart(),
			],
			'custom_prices' => [
				'enabled' => false,
				'list' => [],
			],
			'minimum_price' => $this->get_minimum_price(),
		];

		$now = ( new \DateTime( 'now', $this->get_post()->get_timezone() ) );
		$props['today'] = [
			'date' => $now->format('Y-m-d'),
			'time' => $now->format('H:i:s'),
		];

		$base_price = $this->get_product_field('base-price');
		if ( $base_price ) {
			$props['base_price'] = [
				'enabled' => true,
				'amount' => $value['base_price']['amount'],
				'discount_amount' => $value['base_price']['discount_amount'],
			];
		}

		if ( $custom_prices = $this->get_product_field('custom-prices') ) {
			$props['custom_prices']['enabled'] = true;
			$props['custom_prices']['list'] = $custom_prices->get_custom_prices();
		}

		return $props;
	}

	public static function is_repeatable(): bool {
		return false;
	}

	public static function is_singular(): bool {
		return true;
	}

	public function get_field_templates() {
		$templates = [];

		foreach ( $this->get_supported_product_types() as $product_type ) {
			foreach ( $product_type->repository->get_product_fields() as $field ) {
				$field = clone $field;
				$field->set_product_field( $this );
				if ( $template = locate_template( sprintf( 'templates/widgets/create-post/product-field/%s-field.php', $field->get_key() ) ) ) {
					$templates[] = $template;
				}

				$templates = array_merge( $templates, $field->get_field_templates() );
			}
		}

		return $templates;
	}

	public function check_dependencies() {
		$product_type = $this->get_product_type();
		if ( ! $product_type ) {
			throw new \Exception( 'Product type not set.' );
		}
	}

	/**
	 * Is product available for purchase.
	 *
	 * @since 1.4.0
	 */
	public function is_available(): bool {
		try {
			$this->check_product_form_validity();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Check whether this product has the minimum configuration
	 * needed to be considered available for purchase.
	 *
	 * @since 1.4.0
	 */
	public function check_product_form_validity() {
		$product_type = $this->get_product_type();
		if ( ! $product_type ) {
			throw new \Exception( _x( 'Product not available', 'products', 'voxel' ) );
		}

		if ( $this->post->get_status() !== 'publish' ) {
			throw new \Exception( _x( 'Product not available', 'products', 'voxel' ) );
		}

		$value = $this->get_value();
		if ( ! $value['enabled'] ) {
			throw new \Exception( _x( 'Product not available', 'products', 'voxel' ) );
		}

		if ( ! $this->post->get_author() ) {
			throw new \Exception( _x( 'Product not available', 'products', 'voxel' ) );
		}

		if ( $product_type->supports_marketplace() ) {
			$vendor = $this->post->get_author();
			if ( ! ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) ) {
				$vendor_account = $vendor->get_stripe_vendor_details();
				if ( ! $vendor_account->charges_enabled ) {
					throw new \Exception( _x( 'Product not available', 'products', 'voxel' ) );
				}
			}
		}

		if ( $this->is_shippable() ) {
			$vendor = $this->post->get_author();

			if (
				\Voxel\get( 'payments.provider' ) === 'stripe'
				&& \Voxel\get( 'payments.stripe.stripe_connect.enabled' )
				&& $product_type->supports_marketplace()
				&& \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor'
				&& ! ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true )
			) {
				if ( empty( $vendor->get_vendor_shipping_zones() ) ) {
					if ( get_current_user_id() === $vendor->get_id() ) {
						throw new \Exception( sprintf(
							_x( 'No shipping zones available.<br>You can configure shipping through your <a href="%s" target="_blank">vendor dashboard</a>.', 'products', 'voxel' ),
							esc_url( get_permalink( \Voxel\get( 'templates.stripe_account' ) ) ?: home_url('/') )
						), \VOXEL\PRODUCT_ERR_NO_VENDOR_SHIPPING_ZONES );
					} else {
						throw new \Exception( _x( 'Shipping not available', 'products', 'voxel' ), \VOXEL\PRODUCT_ERR_NO_VENDOR_SHIPPING_ZONES );
					}
				}
			} else {
				if ( empty( \Voxel\Product_Types\Shipping\Shipping_Zone::get_all() ) ) {
					throw new \Exception( _x( 'Shipping not available', 'products', 'voxel' ), \VOXEL\PRODUCT_ERR_NO_PLATFORM_SHIPPING_ZONES );
				}
			}
		}

		do_action( 'voxel/products/check_product_form_validity', $this );

		try {
			foreach ( $this->get_product_fields() as $field ) {
				$field->check_product_form_validity( $value );
			}
		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage() ?: _x( 'Product not available', 'products', 'voxel' ), $e->getCode() );
		}
	}

	public function get_currency() {
		if ( $this->get_product_field('currency') ) {
			$currency = $this->get_value()['currency'];

			if ( $currency !== null ) {
				return strtoupper( $currency );
			}
		}

		return strtoupper( \Voxel\get_primary_currency() );
	}

	public function can_be_added_to_cart(): bool {
		$product_type = $this->get_product_type();
		if ( ! in_array( $product_type->get_product_mode(), [ 'regular', 'variable' ], true ) ) {
			return false;
		}

		if ( $product_type->config('settings.payments.mode') !== 'payment' ) {
			return false;
		}

		if ( ! $product_type->config( 'modules.cart.enabled', true ) ) {
			return false;
		}

		if ( $product_type->supports_marketplace() ) {
			if ( \Voxel\get('payments.stripe.stripe_connect.charge_type') === 'destination_charges' ) {
				$vendor = $this->post->get_author();
				if ( ! $vendor->has_cap('administrator') ) {
					return false;
				}

				if ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) === true ) {
					return false;
				}
			}
		}

		if (
			! $product_type->supports_marketplace()
			&& \Voxel\get( 'product_settings.orders.managed_by', 'product_author' ) === 'product_author'
		) {
			$vendor = $this->post->get_author();
			if ( ! $vendor->has_cap('administrator') ) {
				return false;
			}

			if ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) === true ) {
				return false;
			}
		}

		if ( strtoupper( $this->get_currency() ) !== strtoupper( \Voxel\get_primary_currency() ) ) {
			return false;
		}

		return true;
	}

	public function supports_one_click_add_to_cart(): bool {
		if ( ! $this->can_be_added_to_cart() ) {
			return false;
		}

		if ( $this->get_product_type()->get_product_mode() !== 'regular' ) {
			return false;
		}

		if ( $addons = $this->get_product_field('addons') ) {
			foreach ( $addons->get_addons() as $addon ) {
				if ( $addon->is_required() && $addon->is_active() ) {
					return false;
				}
			}
		}

		return true;
	}

	public function is_shippable(): bool {
		$shipping = $this->get_product_field( 'shipping' );
		if ( ! $shipping ) {
			return false;
		}

		$config = $this->get_value();
		if ( ! ( $this->product_type->config('modules.shipping.required') || $config['shipping']['enabled'] ) ) {
			return false;
		}

		return true;
	}

	public function is_in_stock(): bool {
		if ( ! $this->product_type ) {
			return false;
		}

		if ( $this->get_product_field('stock') ) {
			$value = $this->get_value();
			return ! ( $value['stock']['enabled'] && $value['stock']['quantity'] < 1 );
		}

		if ( $this->get_product_field('variations') ) {
			$variations = $this->get_form_field('form-variations')->get_enabled_variations();

			foreach ( $variations as $variation ) {
				if ( $variation['_status'] === 'active' ) {
					return true;
				}
			}

			return false;
		}

		return false;
	}
}
