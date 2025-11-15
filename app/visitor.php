<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Visitor {

	private static $instance;

	public static function get() {
		if ( static::$instance === null ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	public function get_user_agent() {
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	/**
	 * Get user IP address if available in $_SERVER.
	 *
	 * @since 1.3
	 */
	public function get_ip() {
		$keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $keys as $key ) {
			if ( empty( $_SERVER[ $key ] ) ) {
				continue;
			}

			$ip = $_SERVER[ $key ];
			if ( ! is_string( $ip ) ) {
				continue;
			}

			$ip = trim( $ip );

			// sometimes multiple ips are returned in comma-separated format, grab the first one
			$ip = explode( ',', $ip )[0];

			// sometimes the IP contains a port number, remove it
			$ip = explode( ':', $ip )[0];

			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return null;
	}

	/**
	 * Get referrer URL if available in $_SERVER.
	 *
	 * @since 2.0
	 */
	public function get_referrer() {
		if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
			return null;
		}

		$url = $_SERVER['HTTP_REFERER'];
		$parts = parse_url( $url );

		if ( $parts === false || empty( $parts['host'] ) ) {
			return null;
		}

		return [
			'url' => $url,
			'domain' => $parts['host'],
		];
	}

	/**
	 * Get user OS info based on $_SERVER['HTTP_USER_AGENT'].
	 *
	 * @since 1.3
	 */
	public function get_os(): ?string {
		$user_agent = $this->get_user_agent();
		$os_array = [
			'/iphone|ipad|ipod/i'   => 'ios',
			'/android/i'            => 'android',
			'/windows|win32/i'      => 'windows',
			'/macintosh|mac os x/i' => 'macos',
			'/linux/i'              => 'linux',
			'/ubuntu/i'             => 'ubuntu',
			'/webos/i'              => 'webos',
		];

		foreach ( $os_array as $regex => $value ) {
			if ( preg_match( $regex, $user_agent ) ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Get user browser info based on $_SERVER['HTTP_USER_AGENT'].
	 *
	 * @since 1.3
	 */
	public function get_browser(): ?string {
		$user_agent = $this->get_user_agent();
		$browser_array  = [
			'/chrome/i'  => 'chrome',
			'/firefox/i' => 'firefox',
			'/safari/i'  => 'safari',
			'/edge/i'    => 'edge',
			'/opera/i'   => 'opera',
			'/msie/i'    => 'ie',
		];

		foreach ( $browser_array as $regex => $value ) {
			if ( preg_match( $regex, $user_agent ) ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Try to retrieve country code from cookies or headers.
	 *
	 * @since 1.3
	 */
	public function get_country(): ?array {
		$list = \Voxel\Utils\Country_List::all();

		$code = $_COOKIE['_vx_ccode'] ?? null;
		if ( is_string( $code ) && isset( $list[ strtoupper( $code ) ] ) ) {
			return $list[ strtoupper( $code ) ];
		}

		$headers = apply_filters( 'voxel/ipgeo/country-code-headers', [
			'MM_COUNTRY_CODE',
			'GEOIP_COUNTRY_CODE',
			'HTTP_CF_IPCOUNTRY',
			'HTTP_X_COUNTRY_CODE',
		] );

		foreach ( $headers as $header ) {
			$code = $_SERVER[ $header ] ?? null;
			if ( is_string( $code ) && isset( $list[ strtoupper( $code ) ] ) ) {
				return $list[ strtoupper( $code ) ];
			}
		}

		return null;
	}

	/**
	 * Get visitor's browser language code from headers.
	 *
	 * @link  https://www.dyeager.org/2008/10/getting-browser-default-language-php.html
	 * @since 2.0
	 */
	public function get_language( $default = 'en') {
		if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return null;
		}

		// Split possible languages into array
		$x = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		foreach ( $x as $val ) {
			// check for q-value and create associative array. No q-value means 1 by rule
			if ( preg_match( "/(.*);q=([0-1]{0,1}.\d{0,4})/i", $val, $matches ) ) {
				$lang[$matches[1]] = (float) $matches[2];
			} else {
				$lang[$val] = 1.0;
			}
		}

		// return default language (highest q-value)
		$qval = 0.0;
		foreach ( $lang as $key => $value ) {
			if ( $value > $qval ) {
				$qval = (float) $value;
				$default = $key;
			}
		}

		return $default;
	}

	/**
	 * Generate a unique id from available data to identify unique visitors.
	 *
	 * @since 1.3
	 */
	public function get_unique_id() {
		return mb_substr( md5( json_encode( [
			$this->get_user_agent(),
			$this->get_ip(),
			$this->get_language(),
		] ) ), 0, 9 );
	}
}
