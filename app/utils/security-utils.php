<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

function verify_recaptcha( $token, $action ) {
	$secret = \Voxel\get( 'settings.recaptcha.secret' );

	if ( empty( $token ) || empty( $secret ) ) {
		throw new \Exception( _x( 'Missing security token.', 'recaptcha', 'voxel' ) );
	}

	$response = wp_remote_get( add_query_arg( [
		'secret'   => $secret,
		'response' => $token,
		'remoteip' => isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
	], 'https://www.google.com/recaptcha/api/siteverify' ) );

	if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
		throw new \Exception( _x( 'Security check failed.', 'recaptcha', 'voxel' ) );
	}

	$json = json_decode( $response['body'] );
	if ( ! ( $json && $json->success && $json->action === $action && $json->score >= 0.5 ) ) {
		throw new \Exception( _x( 'Could not verify captcha.', 'recaptcha', 'voxel' ) );
	}
}

function verify_nonce( $nonce, $action ) {
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		throw new \Exception( __( 'Could not verify request.', 'voxel' ) );
	}
}

function validate_username( $username ) {
	if ( empty( $username ) ) {
		throw new \Exception( _x( 'Please enter a username.', 'auth', 'voxel' ), 101 );
	}

	if ( ! \validate_username( $username ) ) {
		throw new \Exception( _x( 'Please enter a valid username.', 'auth', 'voxel' ), 102 );
	}

	if ( username_exists( $username ) ) {
		throw new \Exception( _x( 'This username is already registered. Please choose another one.', 'auth', 'voxel' ), 103 );
	}

	$illegal_user_logins = (array) apply_filters( 'illegal_user_logins', [] );
	if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_user_logins ), true ) ) {
		throw new \Exception( _x( 'This username is not allowed.', 'auth', 'voxel' ), 104 );
	}
}

function validate_password( $password ) {
	if ( mb_strlen( $password ) < 8 ) {
		throw new \Exception( _x( 'Password must contain at least 8 characters.', 'validate password', 'voxel' ) );
	}

	if ( ! preg_match( '/[0-9]+/', $password ) ) {
		throw new \Exception( _x( 'Password must contain at least one number.', 'validate password', 'voxel' ) );
	}

	if ( ! preg_match( '/[A-Za-z]+/', $password ) ) {
		throw new \Exception( _x( 'Password must contain at least one letter.', 'validate password', 'voxel' ) );
	}
}

function validate_user_email( $email ) {
	if ( empty( $email ) ) {
		throw new \Exception( _x( 'Please enter your email address.', 'auth', 'voxel' ), 105 );
	}

	if ( ! is_email( $email ) ) {
		throw new \Exception( _x( 'Please enter a valid email address.', 'auth', 'voxel' ), 106 );
	}

	if ( email_exists( $email ) ) {
		throw new \Exception( _x( 'This email is already registered.', 'auth', 'voxel' ), 107 );
	}
}

function generate_username_from_email( string $email ): ?string {
	$username = stristr( $email, '@', true );
	$username = substr( $username, 0, 48 );
	$username = \sanitize_user( $username, true );

	if ( empty( $username ) ) {
		$username = sprintf( 'user%s', mt_rand( 1000, 9999 ) );
	}

	if ( get_user_by( 'login', $username ) ) {
		do {
			$username .= mt_rand(1, 9);
		} while ( get_user_by( 'login', $username ) );
	}

	return $username;
}
