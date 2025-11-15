<?php

namespace Voxel\Controllers\Frontend\Products;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.add_to_cart', '@add_to_cart' );
		$this->on( 'voxel_ajax_products.get_cart_items', '@get_cart_items' );
		$this->on( 'voxel_ajax_products.remove_cart_item', '@remove_cart_item' );
		$this->on( 'voxel_ajax_products.empty_cart', '@empty_cart' );
		$this->on( 'voxel_ajax_products.update_cart_item_quantity', '@update_cart_item_quantity' );

		$this->on( 'voxel_ajax_nopriv_products.get_guest_cart_items', '@get_guest_cart_items' );
		$this->on( 'voxel_ajax_nopriv_products.add_to_guest_cart', '@add_to_guest_cart' );
		$this->on( 'voxel_ajax_nopriv_products.update_guest_cart_item_quantity', '@update_guest_cart_item_quantity' );

		$this->on( 'voxel_ajax_products.add_to_cart_quick_action', '@add_to_cart_quick_action' );
		$this->on( 'voxel_ajax_nopriv_products.add_to_cart_quick_action', '@add_to_cart_quick_action' );

		$this->on( 'voxel_ajax_nopriv_products.quick_register.send_confirmation_code', '@quick_register_send_confirmation_code' );
		$this->on( 'voxel_ajax_nopriv_products.quick_register.process', '@quick_register_process' );
	}

	protected function add_to_cart() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_cart' );

			$payload = json_decode( wp_unslash( $_REQUEST['item'] ?? '' ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 100 );
			}

			$cart = \Voxel\current_user()->get_cart();
			$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $payload );

			$cart->add_item( $cart_item );
			$cart->update();

			return wp_send_json( [
				'success' => true,
				'item' => $cart_item->get_frontend_config(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function get_cart_items() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_cart' );

			$this->_persist_guest_cart();

			$cart = \Voxel\current_user()->get_cart();

			return wp_send_json( [
				'success' => true,
				'items' => array_map( function( $item ) {
					return $item->get_frontend_config();
				}, $cart->get_items() ),
				'checkout_link' => get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/'),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function remove_cart_item() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_cart' );

			$item_key = sanitize_text_field( $_REQUEST['item_key'] ?? null );
			if ( $item_key !== null ) {
				$cart = \Voxel\current_user()->get_cart();
				$cart->remove_item( $item_key );
				$cart->update();
			}

			return wp_send_json( [
				'success' => true,
			] );

		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function update_cart_item_quantity() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_cart' );

			$item_key = sanitize_text_field( $_REQUEST['item_key'] ?? null );
			$quantity = absint( $_REQUEST['item_quantity'] ?? null );

			$cart = \Voxel\current_user()->get_cart();
			$cart->set_item_quantity( $item_key, $quantity );
			$cart->update();

			return wp_send_json( [
				'success' => true,
				'item' => $cart->get_item( $item_key )->get_frontend_config(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function empty_cart() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_cart' );

			$cart = \Voxel\current_user()->get_cart();
			$cart->empty();
			$cart->update();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function get_guest_cart_items() {
		try {
			$cart = $this->_get_guest_cart();
			// $auth_link = get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/');
			// $checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');

			return wp_send_json( [
				'success' => true,
				'items' => array_map( function( $item ) {
					return $item->get_frontend_config();
				}, $cart->get_items() ),
				'checkout_link' => get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/'),
				// 'checkout_link' => add_query_arg( 'redirect_to', $checkout_link, $auth_link ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function add_to_guest_cart() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			$payload = json_decode( wp_unslash( $_REQUEST['item'] ?? '' ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 100 );
			}

			$cart = $this->_get_guest_cart();
			$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $payload );

			$cart->add_item( $cart_item );

			return wp_send_json( [
				'success' => true,
				'item' => $cart_item->get_frontend_config(),
				'guest_cart' => array_map( function( $item ) {
					return $item->get_value();
				}, $cart->get_items() ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function update_guest_cart_item_quantity() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			$item_key = sanitize_text_field( $_REQUEST['item_key'] ?? null );
			$quantity = absint( $_REQUEST['item_quantity'] ?? null );

			$cart = $this->_get_guest_cart();
			$cart->set_item_quantity( $item_key, $quantity );
			$cart->update();

			return wp_send_json( [
				'success' => true,
				'item' => $cart->get_item( $item_key )->get_frontend_config(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function _get_guest_cart() {
		$cart = new \Voxel\Product_Types\Cart\Guest_Cart;

		$items = (array) json_decode( wp_unslash( $_REQUEST['guest_cart'] ?? '' ), true );
		$items = array_slice( (array) $items, 0, 30 );

		foreach ( $items as $key => $value ) {
			try {
				$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $value, $key );
				$cart->add_item( $cart_item );
			} catch ( \Exception $e ) {}
		}

		return $cart;
	}

	protected function _persist_guest_cart() {
		$cart = \Voxel\current_user()->get_cart();
		$guest_cart = $this->_get_guest_cart();
		$has_new_items = false;

		foreach ( $guest_cart->get_items() as $item ) {
			try {
				$cart->add_item( $item );
				$has_new_items = true;
			} catch ( \Exception $e ) {}
		}

		if ( $has_new_items ) {
			$cart->update();
		}
	}

	protected function add_to_cart_quick_action() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			$post_id = $_REQUEST['product_id'] ?? null;
			if ( ! is_numeric( $post_id ) ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 101 );
			}

			$post = \Voxel\Post::get( $post_id );
			if ( ! $post ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 102 );
			}

			$field = $post->get_field( 'product' );
			if ( ! ( $field && $field->get_type() === 'product' ) ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 103 );
			}

			try {
				$field->check_product_form_validity();
			} catch ( \Exception $e ) {
				throw new \Exception( __( 'This product is not available at the moment', 'voxel' ), 104 );
			}

			if ( ! $field->supports_one_click_add_to_cart() ) {
				throw new \Exception( __( 'This product cannot be added to cart', 'voxel' ), 105 );
			}

			$schema = $field->get_product_form_schema();

			$schema->get_prop('product')->get_prop('post_id')->set_value( $post->get_id() );
			$schema->get_prop('product')->get_prop('field_key')->set_value( $field->get_key() );

			$payload = $schema->export();

			if ( is_user_logged_in() ) {
				$cart = \Voxel\current_user()->get_cart();
				$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $payload );
				$cart->add_item( $cart_item );
				$cart->update();

				return wp_send_json( [
					'success' => true,
					'item' => $cart_item->get_frontend_config(),
				] );
			} else {
				$cart = $this->_get_guest_cart();
				$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $payload );
				$cart->add_item( $cart_item );

				return wp_send_json( [
					'success' => true,
					'item' => $cart_item->get_frontend_config(),
					'guest_cart' => array_map( function( $item ) {
						return $item->get_value();
					}, $cart->get_items() ),
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function quick_register_send_confirmation_code() {
		try {
			if ( \Voxel\get( 'product_settings.cart_summary.guest_customers.behavior', 'proceed_with_email' ) !== 'proceed_with_email' ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ) );
			}

			if ( ! \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.require_verification', true ) ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ) );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_checkout' );
			if ( \Voxel\get('settings.recaptcha.enabled') ) {
				\Voxel\verify_recaptcha( $_REQUEST['_recaptcha'] ?? '', 'vx_checkout_send_confirmation_code' );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

			if ( empty( $email ) || ! is_email( $email ) ) {
				throw new \Exception( _x( 'Please enter a valid email address.', 'auth', 'voxel' ), 106 );
			}

			if ( email_exists( $email ) ) {
				return wp_send_json( [
					'success' => true,
					'message' => _x( 'This email is already registered.', 'auth', 'voxel' ),
					'status' => 'email_exists',
				] );
			}

			\Voxel\Auth\send_confirmation_code( $email, mt_rand( 100000, 999999) );

			return wp_send_json( [
				'success' => true,
				'message' => \Voxel\replace_vars( _x( 'Confirmation code sent to @email', 'auth', 'voxel' ), [
					'@email' => $email,
				] ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function quick_register_process() {
		try {
			if ( \Voxel\get( 'product_settings.cart_summary.guest_customers.behavior', 'proceed_with_email' ) !== 'proceed_with_email' ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ) );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_checkout' );
			if ( \Voxel\get('settings.recaptcha.enabled') ) {
				\Voxel\verify_recaptcha( $_REQUEST['_recaptcha'] ?? '', 'vx_checkout_quick_register' );
			}

			if ( \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.require_tos', false ) ) {
				if ( ( $_REQUEST['terms_agreed'] ?? false ) !== 'yes' ) {
					throw new \Exception( _x( 'You must agree to terms and conditions to proceed.', 'cart summary', 'voxel' ), 108 );
				}
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
			\Voxel\validate_user_email( $email );

			$username = \Voxel\generate_username_from_email( $email );
			\Voxel\validate_username( $username );

			$password = wp_generate_password(16);

			if ( \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.require_verification', true ) ) {
				\Voxel\Auth\verify_confirmation_code( $email, sanitize_text_field( $_POST['_confirmation_code'] ?? '' ) );
			}

			// create user
			$user_id = wp_insert_user( [
				'user_login' => $username,
				'user_email' => $email,
				'user_pass' => $password,
				'role' => apply_filters( 'voxel/guest-checkout/default-role', 'subscriber' ),
			] );

			if ( is_wp_error( $user_id ) ) {
				throw new \Exception( $user_id->get_error_message(), 109 );
			}

			$user = \Voxel\User::get( $user_id );

			// needed for wp_create_nonce() to take cookie token into account in this request
			add_action( 'set_logged_in_cookie', function( $cookie ) {
				$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
			} );

			$wp_user = wp_signon( [
				'user_login' => $user->get_username(),
				'user_password' => $password,
				'remember' => ( $_POST['remember'] ?? null ) === 'no' ? false : true,
			], is_ssl() );

			if ( is_wp_error( $wp_user ) ) {
				throw new \Exception( wp_strip_all_tags( $wp_user->get_error_message() ), 110 );
			}

			do_action( 'voxel/user-registered', $user_id );
			( new \Voxel\Events\Membership\User_Registered_Event )->dispatch( $user_id );

			wp_set_current_user( $user_id );

			$this->_persist_guest_cart();

			if ( \Voxel\get( 'product_settings.cart_summary.guest_customers.proceed_with_email.email_account_details', true ) ) {
				\Voxel\Utils\Async_Requests\Async_Email::instance()->data( [ 'emails' => [ [
					'recipient' => $email,
					'subject' => _x( 'Your account details', 'fast checkout registration email', 'voxel' ),
					'message' => \Voxel\replace_vars( _x( 'Your account has been created successfully.<br>Email: @email<br>Username: @username<br>Password: @password<br><a href="@login_url">Login</a>', 'fast checkout registration email', 'voxel' ), [
						'@username' => $username,
						'@email' => $email,
						'@password' => $password,
						'@login_url' => esc_url( get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/') ),
					] ),
					'headers' => [
						'Content-type: text/html;',
					],
				] ] ] )->dispatch();
			}

			return wp_send_json( [
				'success' => true,
				'nonces' => [
					'cart' => wp_create_nonce('vx_cart'),
					'checkout' => wp_create_nonce('vx_checkout'),
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
