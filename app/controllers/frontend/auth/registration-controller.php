<?php

namespace Voxel\Controllers\Frontend\Auth;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Registration_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_nopriv_auth.register', '@register' );
		$this->on( 'voxel_ajax_nopriv_auth.register.resend_confirmation_code', '@resend_confirmation_code' );
		$this->filter( 'voxel/register/redirect_to', '@set_registration_redirect', 10, 3 );
	}

	protected function register() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_auth' );
			if ( \Voxel\get('settings.recaptcha.enabled') ) {
				\Voxel\verify_recaptcha( $_REQUEST['_recaptcha'] ?? '', 'vx_register' );
			}

			$role = \Voxel\Role::get( sanitize_key( wp_unslash( $_POST['role'] ?? '' ) ) );

			if ( ! ( $role && $role->is_registration_enabled() ) ) {
				throw new \Exception( _x( 'Invalid request.', 'auth', 'voxel' ), 100 );
			}

			if ( \Voxel\get( 'settings.membership.username_behavior' ) === 'generate_from_email' ) {
				$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
				\Voxel\validate_user_email( $email );

				$username = \Voxel\generate_username_from_email( $email );
				try {
					\Voxel\validate_username( $username );
				} catch ( \Exception $e ) {
					throw new \Exception( _x( 'Please enter a valid email address.', 'auth', 'voxel' ), 110 );
				}
			} else {
				$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
				\Voxel\validate_user_email( $email );

				$username = sanitize_user( wp_unslash( $_POST['username'] ?? '' ) );
				\Voxel\validate_username( $username );
			}

			$password = (string) ( $_POST['password'] ?? '' );
			\Voxel\validate_password( $password );

			$errors = new \WP_Error;
			$errors = apply_filters( 'voxel/registration-errors', $errors, $username, $email, $role );
			if ( $errors->has_errors() ) {
				throw new \Exception( join( '<br>', $errors->get_error_messages() ) );
			}

			// validate registration form fields
			$registration_fields = \Voxel\Auth\validate_registration_fields( $role, json_decode( stripslashes( $_POST['postdata'] ), true ) );

			if ( ( $_POST['terms_agreed'] ?? false ) !== 'yes' ) {
				throw new \Exception( _x( 'You must agree to terms and conditions to proceed.', 'auth', 'voxel' ), 108 );
			}

			// email verification
			if ( $role->is_verification_required() ) {
				if ( isset( $_POST['_confirmation_code'] ) ) {
					\Voxel\Auth\verify_confirmation_code( $email, sanitize_text_field( $_POST['_confirmation_code'] ) );
				} else {
					\Voxel\Auth\send_confirmation_code( $email );
					return wp_send_json( [
						'success' => true,
						'verification_required' => true,
					] );
				}
			}

			// create user
			$user_id = wp_insert_user( [
				'user_login' => $username,
				'user_email' => $email,
				'user_pass' => $password,
				'role' => $role->get_key(),
			] );

			if ( is_wp_error( $user_id ) ) {
				throw new \Exception( $user_id->get_error_message(), 109 );
			}

			$user = \Voxel\User::get( $user_id );

			$wp_user = wp_signon( [
				'user_login' => $user->get_username(),
				'user_password' => $password,
				'remember' => ( $_POST['remember'] ?? null ) === 'no' ? false : true,
			], is_ssl() );

			if ( is_wp_error( $wp_user ) ) {
				throw new \Exception( wp_strip_all_tags( $wp_user->get_error_message() ), 110 );
			}

			// at this point user is confirmed, store registration fields validated previously
			\Voxel\Auth\save_registration_fields( $user, $registration_fields['fields'], $registration_fields['sanitized'] );

			do_action( 'voxel/user-registered', $user_id );
			( new \Voxel\Events\Membership\User_Registered_Event )->dispatch( $user_id );

			wp_set_current_user( $user_id );

			$redirect_to = home_url('/');
			if ( ! empty( $_REQUEST['redirect_to'] ) && wp_validate_redirect( $_REQUEST['redirect_to'] ) ) {
				$redirect_to = wp_validate_redirect( $_REQUEST['redirect_to'] );
			}

			$redirect_to = apply_filters(
				'voxel/register/redirect_to',
				$redirect_to,
				$user,
				$role,
				$_REQUEST['redirect_to'] ?? null // raw redirect url
			);

			return wp_send_json( [
				'success' => true,
				'redirect_to' => $redirect_to,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function resend_confirmation_code() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_auth' );
			if ( \Voxel\get('settings.recaptcha.enabled') ) {
				\Voxel\verify_recaptcha( $_REQUEST['_recaptcha'] ?? '', 'vx_resend_confirmation_code' );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
			\Voxel\validate_user_email( $email );

			\Voxel\Auth\send_confirmation_code( $email );

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

	protected function set_registration_redirect( $redirect_to, $user, $role ) {
		$after_registration = $role->config( 'registration.after_registration', 'welcome_step' );
		if ( $after_registration === 'welcome_step' ) {
			return add_query_arg( [
				'welcome' => '',
				'redirect_to' => urlencode( $redirect_to ),
			], get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/') );
		} elseif ( $after_registration === 'custom_redirect' ) {
			return \Voxel\render( $role->config( 'registration.custom_redirect', '' ), [
				'user' => \Voxel\Dynamic_Data\Group::User( \Voxel\get_current_user() ),
				'site' => \Voxel\Dynamic_Data\Group::Site(),
			] );
		} else {
			return $redirect_to;
		}
	}
}
