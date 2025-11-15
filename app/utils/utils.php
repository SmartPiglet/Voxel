<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

spl_autoload_register( function( $classname ) {
	$parts = explode( '\\', $classname );
	if ( $parts[0] !== 'Voxel' ) {
		return;
	}

	$parts[0] = 'App';
	$path_parts = array_map( function( $part ) {
		return strtolower( str_replace( '_', '-', $part ) );
	}, $parts );

	$path = join( DIRECTORY_SEPARATOR, $path_parts ) . '.php';
	if ( locate_template( $path ) ) {
		require_once locate_template( $path );
	}
} );

// aliases
class_alias( '\Voxel\Product_Types\Orders\Order', '\Voxel\Order' );
class_alias( '\Voxel\Product_Types\Order_Items\Order_Item', '\Voxel\Order_Item' );
class_alias( '\Voxel\Product_Types\Cart_Items\Cart_Item', '\Voxel\Cart_Item' );
class_alias( '\Voxel\Modules\Paid_Memberships\Plan', '\Voxel\Plan' );

// form_models backward compat (< 1.7)
// @todo remove in 1.8
class_alias( '\Voxel\Utils\Form_Models\Base_Form_Model', '\Voxel\Form_Models\Base_Form_Model' );
class_alias( '\Voxel\Utils\Form_Models\Checkboxes_Model', '\Voxel\Form_Models\Checkboxes_Model' );
class_alias( '\Voxel\Utils\Form_Models\Color_Model', '\Voxel\Form_Models\Color_Model' );
class_alias( '\Voxel\Utils\Form_Models\DTag_Model', '\Voxel\Form_Models\DTag_Model' );
class_alias( '\Voxel\Utils\Form_Models\Form_Models', '\Voxel\Form_Models\Form_Models' );
class_alias( '\Voxel\Utils\Form_Models\Icon_Model', '\Voxel\Form_Models\Icon_Model' );
class_alias( '\Voxel\Utils\Form_Models\Info_Model', '\Voxel\Form_Models\Info_Model' );
class_alias( '\Voxel\Utils\Form_Models\Key_Model', '\Voxel\Form_Models\Key_Model' );
class_alias( '\Voxel\Utils\Form_Models\Media_Model', '\Voxel\Form_Models\Media_Model' );
class_alias( '\Voxel\Utils\Form_Models\Number_Model', '\Voxel\Form_Models\Number_Model' );
class_alias( '\Voxel\Utils\Form_Models\Password_Model', '\Voxel\Form_Models\Password_Model' );
class_alias( '\Voxel\Utils\Form_Models\Radio_Buttons_Model', '\Voxel\Form_Models\Radio_Buttons_Model' );
class_alias( '\Voxel\Utils\Form_Models\Raw_Model', '\Voxel\Form_Models\Raw_Model' );
class_alias( '\Voxel\Utils\Form_Models\Select_Model', '\Voxel\Form_Models\Select_Model' );
class_alias( '\Voxel\Utils\Form_Models\Switcher_Model', '\Voxel\Form_Models\Switcher_Model' );
class_alias( '\Voxel\Utils\Form_Models\Taxonomy_Select_Model', '\Voxel\Form_Models\Taxonomy_Select_Model' );
class_alias( '\Voxel\Utils\Form_Models\Text_Model', '\Voxel\Form_Models\Text_Model' );
class_alias( '\Voxel\Utils\Form_Models\Textarea_Model', '\Voxel\Form_Models\Textarea_Model' );

require_once locate_template( 'app/vendor/autoload.php' );
require_once locate_template( 'app/utils/constants.php' );
require_once locate_template( 'app/utils/app-utils.php' );
require_once locate_template( 'app/utils/auth-utils.php' );
require_once locate_template( 'app/utils/security-utils.php' );
require_once locate_template( 'app/utils/post-utils.php' );
require_once locate_template( 'app/utils/template-utils.php' );
require_once locate_template( 'app/utils/term-utils.php' );
require_once locate_template( 'app/utils/user-utils.php' );
require_once locate_template( 'app/utils/recurring-date-utils.php' );
require_once locate_template( 'app/utils/timeline-utils.php' );
require_once locate_template( 'app/utils/stat-utils.php' );
require_once locate_template( 'app/utils/demo-import-utils.php' );
require_once locate_template( 'app/utils/dev-utils.php' );

function render( $string, $groups = null ) {
	\Voxel\measure_start( 'voxel/dtags/rendering_time' );

	if ( $groups === null ) {
		$groups = \Voxel\_get_default_render_groups();
	}

	$renderer = new \Voxel\Dynamic_Data\VoxelScript\Renderer( $groups );
	$rendered = $renderer->render( $string );

	\Voxel\measure_end( 'voxel/dtags/rendering_time' );
	return $rendered;
}

function _get_default_render_groups(): array {
	$current_user = \Voxel\get_current_user();
	$current_post = \Voxel\get_current_post();
	$current_term = \Voxel\get_current_term();

	return apply_filters( 'voxel/dynamic-data/default-render-groups', [
		'user' => \Voxel\Dynamic_Data\Group::User( $current_user ),
		'post' => \Voxel\Dynamic_Data\Group::Post( $current_post ),
		'author' => \Voxel\Dynamic_Data\Group::User( $current_post ? $current_post->get_author() : null ),
		'site' => \Voxel\Dynamic_Data\Group::Site(),
		'term' => \Voxel\Dynamic_Data\Group::Term( $current_term ),
	] );
}

function classname_to_filename( $classname, $with_namespace = false ) {
	$parts = explode( '\\', $classname );
	return strtolower( str_replace( '_', '-', $with_namespace ? $classname : array_pop( $parts ) ) );
}

function filename_to_classname( $filename ) {
	return str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $filename ) ) );
}

function get_assets_version() {
	static $version;
	if ( ! is_null( $version ) ) {
		return $version;
	}

	$version = \Voxel\is_dev_mode() ? microtime(true) : wp_get_theme( get_template() )->get('Version');
	return $version;
}

function is_elementor_active() {
	return class_exists( '\Elementor\Plugin' );
}

function is_elementor_pro_active() {
	return class_exists( '\ElementorPro\Plugin' );
}

function is_qm_active() {
	return class_exists( '\QueryMonitor' );
}

function is_edit_mode() {
	return \Voxel\is_elementor_active() && \Elementor\Plugin::$instance->editor->is_edit_mode();
}

function is_preview_mode() {
	return \Voxel\is_elementor_active() && \Elementor\Plugin::$instance->preview->is_preview_mode();
}

function is_elementor_ajax() {
	return ! empty( $_REQUEST['_nonce'] ) && wp_verify_nonce( $_REQUEST['_nonce'], 'elementor_ajax' );
}

function is_elementor_preview() {
	return isset( $_GET['elementor-preview'] );
}

function is_rendering_css() {
	return !! ( $GLOBALS['vx_rendering_css'] ?? null );
}

function is_importing_elementor_template() {
	static $is_importing;
	if ( is_null( $is_importing ) ) {
		$is_importing = ( $_REQUEST['action'] ?? null ) === 'elementor_library_direct_actions'
			&& ( $_REQUEST['library_action'] ?? null ) === 'direct_import_template'
			&& wp_verify_nonce( $_REQUEST['_nonce'] ?? null, 'elementor_ajax' );
	}

	return $is_importing;
}

function _is_using_mariadb() {
	global $wpdb;
	$db_version = $wpdb->get_results( "SHOW VARIABLES WHERE `Variable_name` IN ( 'version_comment', 'innodb_version' )", OBJECT_K );

	$str1 = strtolower( $db_version['version_comment']->Value ?? '' );
	$str2 = strtolower( $db_version['innodb_version']->Value ?? '' );
	$str3 = strtolower( $wpdb->get_var( 'SELECT VERSION()' ) );

	if ( str_contains( $str1, '8.0.' ) || str_contains( $str2, '8.0.' ) || str_contains( $str3, '8.0.' ) ) {
		return false;
	}

	return true;
}

function is_using_mariadb() {
	$db_type = \Voxel\get('settings.db.type');

	if ( $db_type === 'mysql' ) {
		return false;
	} elseif ( $db_type === 'mariadb' ) {
		return true;
	} else {
		$db_type = \Voxel\_is_using_mariadb() ? 'mariadb' : 'mysql';
		\Voxel\set( 'settings.db.type', $db_type );

		return $db_type === 'mariadb';
	}
}

function set_rendering_css( bool $is_rendering ) {
	$GLOBALS['vx_rendering_css'] = $is_rendering;
}

function get_image( $image ) {
	return trailingslashit( get_template_directory_uri() ).'assets/images/'.$image;
}

/**
 * Helper; Return "uploads/" full directory path.
 *
 * @since 1.0
 */
function uploads_dir( $path = '' ) {
	return trailingslashit( wp_upload_dir()['basedir'] ).$path;
}

/**
 * Delete given directory.
 *
 * @since 2.2.3
 */
function delete_directory( $target ) {
	if ( is_dir( $target ) ) {
		$files = glob( $target . '*', GLOB_MARK );
		foreach( $files as $file ) {
			delete_directory( $file );
		}

		@rmdir( $target );
	} elseif ( is_file( $target ) ) {
		@unlink( $target );
	}
}

function parse_icon_string( $string ) {
	$string = (string) $string;
	$library = substr( $string, 0, strpos( $string, ':') );
	$icon = substr( $string, strpos( $string, ':') + 1 );

	if ( $library === 'svg' && is_numeric( $icon ) ) {
		$icon = [
			'id' => absint( $icon ),
			'url' => wp_get_attachment_url( $icon ),
		];
	}

	return [
		'value' => $icon,
		'library' => $library,
	];
}

function get_icon_markup( $icon ) {
	if ( ! \Voxel\is_elementor_active() ) {
		return '';
	}

	if ( ! is_array( $icon ) ) {
		$icon = \Voxel\parse_icon_string( $icon );
	}

	\Elementor\Plugin::$instance->frontend->enqueue_font( $icon['library'] );

	if ( $icon['library'] === 'svg' && ! empty( $icon['value']['url'] ) ) {
		if ( ! str_starts_with( pathinfo( $icon['value']['url'], PATHINFO_EXTENSION ), 'svg' ) ) {
			return '';
		}
	}

	ob_start();
	\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
	$markup = ob_get_clean();

	if ( $icon['library'] === 'svg' && ! str_contains( $markup, '<svg' ) ) {
		return '';
	}

	return $markup;
}

function get_icon_markup_optimized( $icon_string ) {
	static $svg_cache = [];

	if ( str_starts_with( (string) $icon_string, 'svg:' ) ) {
		if ( ! isset( $svg_cache[ $icon_string ] ) ) {
			$svg_markup = \Voxel\get_icon_markup( $icon_string );
			$svg_markup = str_replace( ' id=', ' _id=', $svg_markup );
			$svg_markup = str_replace( '<svg', sprintf( '<svg id="ts-svg-%s" ', $icon_string ), $svg_markup );
			$svg_cache[ $icon_string ] = $svg_markup;
		} else {
			$svg_markup = sprintf( '<svg viewBox="0 0 32 32"><use width="32" height="32" href="#ts-svg-%s"></use></svg>', $icon_string );
		}

		return $svg_markup;
	} else {
		return \Voxel\get_icon_markup( $icon_string );
	}
}

function render_icon( $icon ) {
	echo \Voxel\get_icon_markup( $icon );
}

function get_weekdays() {
	return [
		'mon' => _x( 'Monday', 'weekdays', 'voxel' ),
		'tue' => _x( 'Tuesday', 'weekdays', 'voxel' ),
		'wed' => _x( 'Wednesday', 'weekdays', 'voxel' ),
		'thu' => _x( 'Thursday', 'weekdays', 'voxel' ),
		'fri' => _x( 'Friday', 'weekdays', 'voxel' ),
		'sat' => _x( 'Saturday', 'weekdays', 'voxel' ),
		'sun' => _x( 'Sunday', 'weekdays', 'voxel' ),
	];
}

function get_weekdays_short(): array {
	return [
		'mon' => _x( 'Mon', 'weekdays short', 'voxel' ),
		'tue' => _x( 'Tue', 'weekdays short', 'voxel' ),
		'wed' => _x( 'Wed', 'weekdays short', 'voxel' ),
		'thu' => _x( 'Thu', 'weekdays short', 'voxel' ),
		'fri' => _x( 'Fri', 'weekdays short', 'voxel' ),
		'sat' => _x( 'Sat', 'weekdays short', 'voxel' ),
		'sun' => _x( 'Sun', 'weekdays short', 'voxel' ),
	];
}

function get_weekday_indexes() {
	return [
		'mon' => 0,
		'tue' => 1,
		'wed' => 2,
		'thu' => 3,
		'fri' => 4,
		'sat' => 5,
		'sun' => 6,
	];
}

/**
 * Return all registered image sizes.
 *
 * @since 1.0
 */
function get_image_sizes() {
	global $_wp_additional_image_sizes;
	$sizes = [];

	foreach ( [ 'thumbnail', 'medium', 'medium_large', 'large' ] as $size ) {
		$sizes[ $size ] = [
			'width'  => intval( get_option( "{$size}_size_w" ) ),
			'height' => intval( get_option( "{$size}_size_h" ) ),
			'crop'   => get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false,
		];
	}

	if ( ! empty( $_wp_additional_image_sizes ) ) {
		$sizes = array_merge( $sizes, $_wp_additional_image_sizes );
	}

	return $sizes;
}

function get_image_sizes_with_labels() {
	$sizes = [];
	foreach ( \Voxel\get_image_sizes() as $key => $size ) {
		$label = ucwords( str_replace( '_', ' ', $key ) );
		$sizes[ $key ] = sprintf( '%s (%sx%s)', $label, $size['width'], $size['height'] ?: '(auto)' );
	}

	$sizes['full'] = 'Full size';

	return $sizes;
}

/**
 * Check whether the current request is nearing on using maximum
 * execution time and memory.
 *
 * @since 1.0
 */
function nearing_resource_limits(): bool {
	// check if less than 5 seconds of execution time are left
	$max_execution_time = absint( ini_get('max_execution_time') );
	$time_limit = $max_execution_time === 0 ? 60 : min( 60, ( $max_execution_time - 5 ) );
	$time_nearing_limit = ( $time_limit - ( microtime(true) - WP_START_TIMESTAMP ) ) < 0;

	// check if more than 85% of memory has been used (75% if QueryMonitor is active)
	$max_memory_usage = class_exists( '\QueryMonitor' ) ? 0.75 : 0.85;
	$memory_limit = absint( wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) ) * $max_memory_usage );
	$memory_nearing_limit = ( $memory_limit - memory_get_usage() ) < 0;

	return $time_nearing_limit || $memory_nearing_limit;
}

function utc() {
	static $datetime;
	if ( is_null( $datetime ) ) {
		$datetime = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );
	}

	return $datetime;
}

function now() {
	static $datetime;
	if ( is_null( $datetime ) ) {
		$datetime = new \DateTimeImmutable( 'now', wp_timezone() );
	}

	return $datetime;
}

function epoch() {
	static $datetime;
	if ( is_null( $datetime ) ) {
		$datetime = new \DateTimeImmutable( '1970-01-01', new \DateTimeZone( 'UTC' ) );
	}

	return $datetime;
}

function currency_format( $price, $currency, $amount_is_in_cents = true ) {
	static $fraction_formatter, $whole_number_formatter;

	if ( ! is_numeric( $price ) ) {
		return '';
	}

	// convert amount from cents to main currency, unless it's a zero decimal currency
	if ( $amount_is_in_cents && ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
		$price /= 100;
	}

	$custom_format = apply_filters( 'voxel/currency_format', null, $price, $currency );
	if ( $custom_format !== null ) {
		return $custom_format;
	}

	if ( ! class_exists( '\NumberFormatter' ) ) {
		return $currency.' '.$price;
	}

	if ( is_null( $fraction_formatter ) ) {
		$fraction_formatter = new \NumberFormatter( get_locale(), \NumberFormatter::CURRENCY );
	}

	if ( is_null( $whole_number_formatter ) ) {
		$whole_number_formatter = new \NumberFormatter( get_locale(), \NumberFormatter::CURRENCY );
		$whole_number_formatter->setAttribute( \NumberFormatter::MIN_FRACTION_DIGITS, 0 );
	}

	// if the price is a round number (29.00, 45.00, etc.), don't display the decimal portion at all
	if ( intval( $price ) == $price ) {
		return $whole_number_formatter->formatCurrency( $price, (string) $currency );
	}

	return $fraction_formatter->formatCurrency( $price, (string) $currency );
}

function interval_format( $interval, $interval_count ) {
	$count = absint( $interval_count );

	if ( $interval === 'month' && $count === 12 ) {
		$interval = 'year';
		$count = 1;
	}

	if ( $interval === 'day' ) {
		return $count === 1
			? _x( 'daily', 'price interval', 'voxel' )
			: sprintf( _x( 'every %s days', 'price interval', 'voxel' ), number_format_i18n( $count ) );
	} elseif ( $interval === 'week' ) {
		return $count === 1
			? _x( 'weekly', 'price interval', 'voxel' )
			: sprintf( _x( 'every %s weeks', 'price interval', 'voxel' ), number_format_i18n( $count ) );
	} elseif ( $interval === 'month' ) {
		return $count === 1
			? _x( 'monthly', 'price interval', 'voxel' )
			: sprintf( _x( 'every %s months', 'price interval', 'voxel' ), number_format_i18n( $count ) );
	} elseif ( $interval === 'year' ) {
		return $count === 1
			? _x( 'yearly', 'price interval', 'voxel' )
			: sprintf( _x( 'every %s years', 'price interval', 'voxel' ), number_format_i18n( $count ) );
	}
}

function random_string( int $length ) {
	$pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$max = strlen( $pool ) - 1;

	$token = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$random_key = random_int( 0, $max );
		$token .= $pool[ $random_key ];
	}

	return $token;
}

function get_google_auth_link( $role = '', $plan = '' ) {
	return add_query_arg( [
		'response_type' => 'code',
		'client_id' => \Voxel\get( 'settings.auth.google.client_id' ),
		'redirect_uri' => rawurlencode( home_url('/?vx=1&action=auth.google.login') ),
		'scope' => 'openid email',
		'state' => base64_encode( wp_json_encode( [
			'_wpnonce' => wp_create_nonce( 'vx_auth_google' ),
			'redirect_to' => \Voxel\get_redirect_url(),
			'role' => $role,
			'plan' => $plan,
		] ) ),
	], 'https://accounts.google.com/o/oauth2/v2/auth' );
}

function get_redirect_url() {
	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		return wp_validate_redirect( $_REQUEST['redirect_to'], home_url('/') );
	} elseif ( $referrer = wp_get_referer() ) {
		return $referrer;
	} else {
		return home_url('/');
	}
}

function get_auth_url() {
	return get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/');
}

function get_login_url() {
	return get_auth_url();
}

function get_register_url() {
	return add_query_arg( [
		'register' => '',
	], get_auth_url() );
}

function get_logout_url() {
	return add_query_arg( [
		'vx' => 1,
		'action' => 'auth.logout',
		'_wpnonce' => wp_create_nonce( 'vx_auth_logout' ),
	], home_url( '/' ) );
}

function get_main_admin(): ?\Voxel\User {
	return \Voxel\User::get( \Voxel\get('settings.notifications.admin_user') );
}

/**
 * Retrieve the full URL of current request.
 *
 * @since 1.2.8
 */
function get_current_url() {
	$protocol = 'https://';
	if ( ! is_ssl() ) {
		$protocol = 'http://';
	}

	return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function date_format( $timestamp ) {
	if ( $timestamp instanceof \DateTime || $timestamp instanceof \DateTimeImmutable ) {
		$timestamp = $timestamp->getTimestamp() + $timestamp->getOffset();
	}

	return date_i18n( get_option( 'date_format' ), $timestamp );
}

function time_format( $timestamp ) {
	if ( $timestamp instanceof \DateTime || $timestamp instanceof \DateTimeImmutable ) {
		$timestamp = $timestamp->getTimestamp() + $timestamp->getOffset();
	}

	return date_i18n( get_option( 'time_format' ), $timestamp );
}

function datetime_format( $timestamp ) {
	if ( $timestamp instanceof \DateTime || $timestamp instanceof \DateTimeImmutable ) {
		$timestamp = $timestamp->getTimestamp() + $timestamp->getOffset();
	}

	return date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $timestamp );
}

function get_minute_of_week( $datetime ) {
	$day_index = absint( $datetime->format('N') ) - 1;
	$day_start = ( $day_index * 1440 ) + 1440;
	return $day_start + ( absint( $datetime->format('H') ) * 60 ) + absint( $datetime->format('i') );
}

/**
 * Merge overlapping integer ranges.
 *
 * @param Array<Array{0: int, 1: int}> $ranges
 */
function merge_ranges( array $ranges ): array {
	usort( $ranges, function( $a, $b ) {
		return $a[0] - $b[0];
	} );

	$n = 0;
	$len = count( $ranges );
	for ( $i = 1; $i < $len; ++$i ) {
		if ( $ranges[$i][0] > $ranges[$n][1] + 1 ) {
			$n = $i;
		} else {
			if ( $ranges[$n][1] < $ranges[$i][1] ) {
				$ranges[$n][1] = $ranges[$i][1];
			}

			unset( $ranges[$i] );
		}
	}

	return array_values($ranges);
}

function clamp( $number, $min, $max ) {
	return max( $min, min( $max, $number ) );
}

function from_list( $value, array $list, $default = null ) {
	return in_array( $value, $list, true ) ? $value : $default;
}

function evaluate_visibility_rules( $rules ): bool {
	$rule_list = \Voxel\Dynamic_Data\Config::get_visibility_rules();
	foreach ( $rules as $rule_group ) {
		$has_valid_rules = false;

		// all rules in a group must be true for the rule group to pass
		foreach ( $rule_group as $rule_config ) {
			if ( ! isset( $rule_list[ $rule_config['type'] ?? null ] ) ) {
				continue;
			}

			$has_valid_rules = true;
			$rule = new $rule_list[ $rule_config['type'] ];
			unset( $rule_config['type'] );
			$rule->set_args( $rule_config );
			if ( $rule->evaluate() === false ) {
				continue(2);
			}
		}

		// make sure group contains at least one valid rule
		if ( ! $has_valid_rules ) {
			continue;
		}

		// if a single rule group has passed conditions, no more evaluation is necessary
		return true;
	}

	return false;
}

function email_queue() {
	return \Voxel\Utils\Async_Requests\Email_Queue::instance();
}

function email_template( $message ) {
	$custom_template = apply_filters( 'voxel/emails/custom-template', null, $message );
	if ( $custom_template !== null ) {
		return $custom_template;
	}

	ob_start();
	require locate_template( 'templates/emails/default-template.php' );
	$template = ob_get_clean();

	// inline styles
	try {
		$emogrifier = new \Voxel\Utils\Vendor\Emogrifier( $template );
		$rendered_template = $emogrifier->emogrify();
	} catch ( \Exception $e ) {
		// if inline styles can't be applied, use the original markup as the message body
		$rendered_template = $template;
	}

	return $rendered_template;
}

function get_accent_color() {
	$default = '#A239FF';
	if ( ! \Voxel\is_elementor_active() ) {
		return $default;
	}

	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
	if ( ! $kit ) {
		return $default;
	}

	$colors = $kit->get_settings('system_colors');
	if ( ! is_array( $colors ) ) {
		return $default;
	}

	return $colors[3]['color'] ?? $default;
}

function get_default_from_email() {
	$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
	$from_email = 'wordpress@';

	if ( null !== $sitename ) {
		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$from_email .= $sitename;
	}

	return $from_email;
}

function get_default_email_footer_text() {
	return <<<HTML
	<strong>@site(title)</strong><br>
	@site(tagline)
	HTML;
}

function get_email_footer_text() {
	$custom_text = \Voxel\get( 'settings.emails.footer_text' );
	if ( ! empty( $custom_text ) ) {
		return \Voxel\render( $custom_text );
	}

	return \Voxel\render( \Voxel\get_default_email_footer_text() );
}

function qrcode( $text ) {
	$qr = \Voxel\Utils\Vendor\QRCode::getMinimumQRCode( $text, QR_ERROR_CORRECT_LEVEL_L );
	$image = $qr->createImage(8, 16);

	ob_start();
	imagepng( $image );
	$contents = ob_get_clean();
	imagedestroy( $image );

	return 'data:image/png;base64,'.base64_encode( $contents );
}

function svg( $filename, $output = true ) {
	$filename = str_ends_with( $filename, '.svg' ) ? $filename : $filename.'.svg';
	$filepath = locate_template( 'assets/images/svgs/'.$filename );
	if ( ! file_exists( $filepath ) ) {
		return '';
	}

	$svg = file_get_contents( $filepath );

	if ( $output ) {
		echo $svg;
	} else {
		return $svg;
	}
}

function get_svg( $filename ) {
	return \Voxel\svg( $filename, false );
}

// haversine great-circle distance
// @link https://stackoverflow.com/a/10054282/3522553
function st_distance_sphere( $latFrom, $lngFrom, $latTo, $lngTo, $earthRadius = 6371000 ) {
	$latFrom = deg2rad( $latFrom );
	$lngFrom = deg2rad( $lngFrom );
	$latTo = deg2rad( $latTo );
	$lngTo = deg2rad( $lngTo );

	$latDelta = $latTo - $latFrom;
	$lngDelta = $lngTo - $lngFrom;

	$angle = 2 * asin( sqrt( pow( sin( $latDelta / 2 ), 2 ) +
		cos( $latFrom ) * cos( $latTo ) * pow( sin( $lngDelta / 2 ), 2 ) ) );

	return $angle * $earthRadius;
}

// @link https://stackoverflow.com/questions/55936249/get-circle-polygon-circumference-points-latitude-and-longitude
function st_buffer( $center_lat, $center_lng, $radius, $segments = 32 ) {
	$coordinates = [];
    $center_lat_rad = deg2rad( $center_lat );
    $center_lng_rad = deg2rad( $center_lng );

	for ( $i = 0; $i < $segments; $i++ ) {
		$bearing = 2 * M_PI * $i / $segments;
		$angular_distance = $radius / 6371000;

		$lat = asin(
			sin( $center_lat_rad ) * cos( $angular_distance ) +
			cos( $center_lat_rad ) * sin( $angular_distance ) * cos( $bearing )
		);

		$lng = $center_lng_rad + atan2(
			sin($bearing) * sin($angular_distance) * cos($center_lat_rad),
			cos($angular_distance) - sin($center_lat_rad) * sin($lat)
		);

		$lng = fmod( $lng + 3 * M_PI, 2 * M_PI ) - M_PI;
		$coordinates[] = [ rad2deg( $lng ), rad2deg( $lat ) ];
	}

    $coordinates[] = $coordinates[0];

	return [
		'coordinates' => $coordinates,
		'polygon' => sprintf( 'POLYGON((%s))', join( ',', array_map( function( $point ) {
			return $point[1].' '.$point[0];
		}, $coordinates ) ) ),
		'polygon_mariadb' => sprintf( 'POLYGON((%s))', join( ',', array_map( function( $point ) {
			return $point[0].' '.$point[1];
		}, $coordinates ) ) ),
	];
}

function prime_relations_cache( $post_ids, $field ) {
	static $primed = [];

	$post_id__in = join( ',', array_map( 'absint', $post_ids ) );
	$prime_key = sprintf( '%s_%s_%s', $field->get_post_type()->get_key(), $field->get_key(), $post_id__in );
	if ( isset( $primed[ $prime_key ] ) ) {
		return $primed[ $prime_key ];
	}

	$primed[ $prime_key ] = [];

	global $wpdb;

	$relation_key = esc_sql( $field->get_relation_key() );
	$column_key = in_array( $field->get_prop('relation_type'), [ 'has_one', 'has_many' ], true ) ? 'parent_id' : 'child_id';
	$select_key = $column_key === 'child_id' ? 'parent_id' : 'child_id';

	foreach ( $post_ids as $index => $post_id ) {
		$cache_key = sprintf( 'relations:%s:%d:%s', $field->get_relation_key(), $post_id, $select_key );

		if ( wp_cache_get( $cache_key, 'voxel' ) ) {
			unset( $post_ids[ $index ] );
		}
	}

	if ( empty( $post_ids ) ) {
		return [];
	}

	$results = $wpdb->get_results( <<<SQL
		SELECT {$column_key} AS `post_id`, GROUP_CONCAT( {$select_key} SEPARATOR ',') AS `list`
		FROM {$wpdb->prefix}voxel_relations
		JOIN {$wpdb->posts} AS p ON ( {$select_key} = p.ID AND p.post_status <> 'trash' )
		WHERE relation_key = '{$relation_key}'
			AND {$column_key} IN ({$post_id__in})
		GROUP BY {$column_key}
	SQL, OBJECT_K );

	$total_ids = [];

	foreach ( $post_ids as $post_id ) {
		$cache_key = sprintf( 'relations:%s:%d:%s', $field->get_relation_key(), $post_id, $select_key );
		$ids = [];

		if ( ! empty( $results[ $post_id ]->list ?? '' ) ) {
			$ids = array_map( 'absint', explode( ',', $results[ $post_id ]->list ?? '' ) );
			$total_ids = array_merge( $total_ids, $ids );
		}

		wp_cache_set( $cache_key, $ids, 'voxel' );
	}

	$total_ids = array_unique( $total_ids );

	$primed[ $prime_key ] = $total_ids;

	return $total_ids;
}

function prime_user_following_cache( int $user_id, array $object_ids, string $object_type ) {
	static $primed = [];

	if ( ! in_array( $object_type, [ 'user', 'post' ], true ) ) {
		return;
	}

	$object_ids = array_filter( array_map( 'absint', $object_ids ) );
	if ( empty( $object_ids ) ) {
		return;
	}

	global $wpdb;

	$object_id__in = join( ',', $object_ids );

	if ( isset( $primed[ $user_id ][ $object_type.'_'.$object_id__in ] ) ) {
		return;
	}

	if ( ! isset( $primed[ $user_id ] ) ) {
		$primed[ $user_id ] = [];
	}

	$primed[ $user_id ][ $object_type.'_'.$object_id__in ] = true;

	// run query
	$results = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT CONCAT_WS( '_', object_type, object_id ) AS `object`, `status` FROM {$wpdb->prefix}voxel_followers
			WHERE `object_type` = '%s'
				AND `object_id` IN ({$object_id__in})
				AND `follower_type` = 'user'
				AND `follower_id` = %d
				AND status = 1
	SQL, $object_type, $user_id ), OBJECT_K );

	$cache_values = [];

	foreach ( $object_ids as $object_id ) {
		$key = sprintf( '%s_%s', $object_type, $object_id );
		$value = $results[ $key ]->status ?? null;
		if ( $value !== null ) {
			$value = intval( $value );
		} else {
			$value = '';
		}

		$cache_values[ $key ] = $value;
	}

	$cache_key = sprintf( 'user_following:%d', $user_id );
	$existing_cache = wp_cache_get( $cache_key, 'voxel' );

	if ( is_array( $existing_cache ) ) {
		$cache_values = array_merge( $existing_cache, $cache_values );
	}

	wp_cache_set( $cache_key, $cache_values, 'voxel' );
}

function get_range_presets( $key = null ) {
	static $ranges;

	if ( $ranges === null ) {
		$ranges = apply_filters( 'voxel/filters/date/range-presets', [
			'all' => [
				'key' => 'all',
				'label' => _x( 'All', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => '1000-01-01',
						'end' => '9999-12-31',
					];
				},
			],
			'upcoming' => [
				'key' => 'upcoming',
				'label' => _x( 'Upcoming', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => '9999-12-31 23:59:59',
					];
				},
			],
			'today' => [
				'key' => 'today',
				'label' => _x( 'Today', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'tomorrow' => [
				'key' => 'tomorrow',
				'label' => _x( 'Tomorrow', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->modify( '+1 day' )->format( 'Y-m-d 00:00:00' ),
						'end' => $now->modify( '+1 day' )->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'this-weekend' => [
				'key' => 'this-weekend',
				'label' => _x( 'This weekend', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					$is_weekend = $now->getTimestamp() > $now->modify('this saturday')->getTimestamp();
					return [
						'start' => $is_weekend ? $now->format( 'Y-m-d H:i:s' ) : $now->modify('this saturday')->format( 'Y-m-d 00:00:00' ),
						'end' => $now->modify('this sunday')->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'this-week' => [
				'key' => 'this-week',
				'label' => _x( 'This week', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					$start_of_week = (int) get_option( 'start_of_week' );
					$weekdays = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];

					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->modify( 'next '.$weekdays[ $start_of_week ] )->modify( '-1 day' )->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'next-week' => [
				'key' => 'next-week',
				'label' => _x( 'Next week', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					$start_of_week = (int) get_option( 'start_of_week' );
					$weekdays = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
					return [
						'start' => $now->modify( 'next '.$weekdays[ $start_of_week ] )->format( 'Y-m-d 00:00:00' ),
						'end' => $now->modify( 'next '.$weekdays[ $start_of_week ] )->modify('+6 days')->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'next-7-days' => [
				'key' => 'next-7-days',
				'label' => _x( 'Next 7 days', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->modify( '+6 days' )->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'this-month' => [
				'key' => 'this-month',
				'label' => _x( 'This month', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->format( 'Y-m-t 23:59:59' ),
					];
				},
			],
			'next-month' => [
				'key' => 'next-month',
				'label' => _x( 'Next month', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->modify('first day of next month')->format( 'Y-m-d 00:00:00' ),
						'end' => $now->modify('first day of next month')->format( 'Y-m-t 23:59:59' ),
					];
				},
			],
			'next-30-days' => [
				'key' => 'next-30-days',
				'label' => _x( 'Next 30 days', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->modify( '+29 days' )->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
			'this-year' => [
				'key' => 'this-year',
				'label' => _x( 'This year', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->format( 'Y-12-31 23:59:59' ),
					];
				},
			],
			'next-year' => [
				'key' => 'next-year',
				'label' => _x( 'Next year', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->modify('+1 year')->format( 'Y-01-01 00:00:00' ),
						'end' => $now->modify('+1 year')->format( 'Y-12-31 23:59:59' ),
					];
				},
			],
			'next-365-days' => [
				'key' => 'next-365-days',
				'label' => _x( 'Next 365 days', 'range presets', 'voxel' ),
				'callback' => function( $now ) {
					return [
						'start' => $now->format( 'Y-m-d H:i:s' ),
						'end' => $now->modify( '+364 days' )->format( 'Y-m-d 23:59:59' ),
					];
				},
			],
		] );
	}

	if ( $key === null ) {
		return $ranges;
	}

	return $ranges[ $key ] ?? null;
}

function get_visitor_os() {
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	if ( empty( $user_agent ) ) {
		return null;
	}

	$list = [
		'/windows/i' => 'Windows',
		'/macintosh|mac os x/i' => 'macOS',
		'/linux/i' => 'Linux',
		'/ubuntu/i' => 'Ubuntu',
		'/iphone|ipad|ipod/i' => 'iOS',
		'/android/i' => 'Android',
		'/webos/i' => 'webOS'
	];

	foreach ( $list as $regex => $os ) {
		if ( preg_match( $regex, $user_agent ) ) {
			return $os;
		}
	}
}

function get_default_stopwords(): string {
	return apply_filters(
		'voxel/keyword-search/default-stopwords',
		'a about an are as at be by com de en for from how i in is it la of on or that the this to was what when where who will with und the www'
	);
}

// innodb default stopwords
function get_stopwords() {
	static $stopwords;
	if ( $stopwords === null ) {
		$list = (string) \Voxel\get( 'settings.db.keyword_search.stopwords', '' );
		if ( empty( $list ) ) {
			$list = \Voxel\get_default_stopwords();
		}

		$words = preg_split('/\s+/', $list );
		$stopwords = [];
		foreach ( $words as $word ) {
			$stopwords[ $word ] = true;
		}
	}

	return $stopwords;
}

function get_keyword_minlength(): int {
	$length = \Voxel\get( 'settings.db.keyword_search.min_word_length', 3 );
	if ( ! is_numeric( $length ) ) {
		return 3; // default
	}

	return max( absint( $length ), 1 );
}

/**
 * Prepare keyword search for use in FULLTEXT columns in non-index
 * tables (wp_posts, wp_users, etc.)
 *
 * @since 1.0
 */
function prepare_keyword_search( string $str ) {
	$stopwords = \Voxel\get_stopwords();
	$min_word_length = \Voxel\get_keyword_minlength();
	$str = mb_substr( $str, 0, apply_filters( 'voxel/keyword-search/max-query-length', 128 ) );

	$keywords = preg_split('/\s+/', str_replace( '@', '', $str ) );
	$keywords = array_map( function( $word ) use ( $stopwords, $min_word_length ) {
		$word = trim( $word, '*+-~<>"()' );
		if ( mb_strlen( $word ) < $min_word_length ) {
			return null;
		}

		if ( isset( $stopwords[ strtolower( $word ) ] ) ) {
			return null;
		}

		return '+'.$word.'*';
	}, $keywords );

	return join( ' ', array_filter( $keywords ) );
}

/**
 * Prepare keyword search for use in post type index tables.
 *
 * @since 1.4.2.4
 */
function prepare_keyword_search_for_index_query( string $str ) {
	$stopwords = \Voxel\get_stopwords();
	$min_word_length = \Voxel\get_keyword_minlength();
	$str = mb_substr( $str, 0, apply_filters( 'voxel/keyword-search/max-query-length', 128 ) );
	$keywords = preg_split('/\s+/', str_replace( '@', '', $str ) );

	$normalized_keywords = [];
	foreach ( $keywords as $keyword ) {
		$keyword = trim( $keyword, '*+-~<>"()' );
		if ( empty( $keyword ) ) {
			continue;
		}

		$normalized = \Voxel\normalize_keyword( $keyword );
		if ( mb_strlen( $normalized ) < $min_word_length ) {
			continue;
		}

		if ( isset( $stopwords[ strtolower( $normalized ) ] ) ) {
			continue;
		}

		$normalized_keywords[ sprintf( '+%s*', $normalized ) ] = true;
	}

	return join( ' ', array_keys( $normalized_keywords ) );
}

function _get_keyword_special_chars(): array {
	static $special_chars = null;
	if ( $special_chars === null ) {
		$special_chars = apply_filters(
			'voxel/keyword-search/strip-keyword-chars',
			[ '-', '.', '\'', '’', '‘', ',', '&', '"', ':', '~', '*', '#' ]
		);
	}

	return $special_chars;
}

function normalize_keyword( string $keyword ) {
	return str_replace( \Voxel\_get_keyword_special_chars(), '', $keyword );
}

/**
 * Prepares keyword filter data for indexing. Removes duplicate words and
 * stores normalized versions of keywords when necessary (strips special
 * characters like dashes, apostrophes, etc.).
 *
 * Normalized keywords are stored alongisde their original versions to account for special cases like:
 *   "Ni-Hao Restaurant" needs to match searches for "Ni-Hao", "Ni Hao" and "Nihao"
 *   "O'Brien" needs to match searches for "O'Brien" and "OBrien"
 *
 * @since 1.4.2.4
 */
function prepare_keyword_data_for_indexing( string $str ) {
	$keywords = preg_split( '/\s+/', $str );

	$normalized_keywords = [];
	foreach ( $keywords as $keyword ) {
		if ( ! empty( $keyword ) ) {
			// needs multibyte-safe trim for left/right single quotation marks (and similar characters)
			// $original = trim( $keyword, join( '', \Voxel\_get_keyword_special_chars() ) );
			$original = \Voxel\mb_trim( $keyword, join( '', \Voxel\_get_keyword_special_chars() ) );
			if ( ! empty( $original ) ) {
				$normalized_keywords[ $original ] = true;
			}
		}

		$normalized = \Voxel\normalize_keyword( $keyword );
		if ( ! empty( $normalized ) ) {
			$normalized_keywords[ $normalized ] = true;
		}
	}

	return join( ' ', array_keys( $normalized_keywords ) );
}

function _get_innodb_ft_min_token_size() {
	global $wpdb;

	$result = $wpdb->get_row( "SHOW VARIABLES LIKE 'innodb_ft_min_token_size'" );
	return is_numeric( $result->Value ?? null ) ? intval( $result->Value ) : null;
}

function mb_str_split( string $string ): array {
	if ( function_exists( '\mb_str_split' ) ) {
		$string = \mb_str_split( $string );
		if ( $string === false ) {
			return []; // mb_str_split() can return false on PHP 7.4
		}

		return $string;
	} else {
		// mb_str_split() is not available in PHP 7.3 and older
		return \preg_split( '//u', $string, -1, PREG_SPLIT_NO_EMPTY );
	}
}

function count_format( $visible_count, $total_count ) {
	if ( $total_count === 0 ) {
		return _x( 'No results', 'post feed', 'voxel' );
	} elseif ( $total_count === 1 ) {
		return _x( 'One result', 'post feed', 'voxel' );
	} elseif ( $total_count <= $visible_count ) {
		return \Voxel\replace_vars( _x( '@count results', 'post feed', 'voxel' ), [
			'@count' => number_format_i18n( $total_count ),
		] );
	} else {
		return \Voxel\replace_vars( _x( '@count out of @total results', 'post feed', 'voxel' ), [
			'@count' => number_format_i18n( $visible_count ),
			'@total' => number_format_i18n( $total_count ),
		] );
	}
}

function enqueue_maps() {
	if ( \Voxel\get('settings.maps.provider') === 'mapbox' && ! \Voxel\is_preview_mode() ) {
		wp_enqueue_script( 'vx:mapbox.js' );
		wp_enqueue_style( 'vx:mapbox.css' );
		wp_enqueue_script( 'mapbox-gl' );
		wp_enqueue_style( 'mapbox-gl' );
	} else {
		wp_enqueue_script( 'vx:google-maps.js' );
		wp_enqueue_script( 'google-maps' );
	}
}

function replace_vars( $str, $vars ) {
	return str_replace( array_keys( $vars ), array_values( $vars ), $str );
}

/**
 * Set the fill color of an SVG string. Mimics the behavior of wp.svgPainter.
 *
 * @since 1.3
 */
function paint_svg( string $svg, string $color ): string {
	if ( ! preg_match( '/fill="(.+?)"/ims', $svg ) ) {
		$svg = str_replace( '<svg', '<svg fill="'.$color.'"', $svg );
	}

	$svg = preg_replace( '/fill="(.+?)"/ims', 'fill="'.$color.'"', $svg );
	$svg = preg_replace( '/style="(.+?)"/ims', 'style="fill:'.$color.'"', $svg );
	$svg = preg_replace( '/fill:.*?;/ims', 'fill:'.$color.'', $svg );
	return $svg;
}

function truncate_text( string $text, int $length = 128 ): string {
	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}

	$text = rtrim( mb_substr( $text, 0, $length ) );
	$text .= "...";

	return $text;
}

function get_ipgeo_providers(): array {
	return apply_filters( 'voxel/ipgeo/providers', [
		[
			'key' => 'geojs.io',
			'label' => 'GeoJS',
			'geocode_url' => 'https://get.geojs.io/v1/ip/country.json',
			'country_code_key' => 'country',
			'description' => 'Free service',
		],
		[
			'key' => 'ipapi.co',
			'label' => 'ipapi',
			'geocode_url' => 'https://ipapi.co/json',
			'country_code_key' => 'country',
			'api_key_param' => 'key',
			'description' => 'Premium service with free tier, request limits: 1k/day, 30k/month',
		],
		[
			'key' => 'ip-api.io',
			'label' => 'IP-API.io',
			'geocode_url' => 'https://ip-api.io/json/',
			'country_code_key' => 'country_code',
			'api_key_param' => 'api_key',
			'description' => 'Premium service with free tier',
		],
	] );
}

function get_permalink_front() {
	global $wp_rewrite;
	return $wp_rewrite->front;
}

function _get_user_scalable_string() {
	$scalable = \Voxel\get( 'settings.perf.user_scalable', 'no' );
	if ( $scalable === 'yes' ) {
		return ', user-scalable=yes';
	} elseif ( $scalable === 'no' ) {
		return ', user-scalable=no';
	} else{
		return '';
	}
}

function evaluate_math_expression( string $expression ) {
	static $executor;
	if ( $executor === null ) {
		$executor = new \Voxel\Vendor\NXP\MathExecutor;
	}

	return $executor->execute( $expression );
}

function is_valid_weekday( $key ): bool {
	$weekdays = \Voxel\get_weekday_indexes();
	return isset( $weekdays[ $key ] );
}

// @link https://www.php.net/manual/en/ref.mbstring.php#113569
function mb_trim( $string, $trim_chars = '\s' ){
	return preg_replace( '/^['.$trim_chars.']*(?U)(.*)['.$trim_chars.']*$/u', '\\1', $string );
}

function trim_spaces( string $string ): string {
	return preg_replace( '/^\s+|\s+$/u', '', $string );
}

function abbreviate_number( $number, $precision = 1 ) {
	if ( ! is_numeric( $number ) ) {
		return null;
	}

	if ( abs( $number ) < 1000 ) {
		return $number;
	}

	$units = ['', 'k', 'm', 'b', 't'];
	$power = floor( ( strlen( (int) $number ) - 1 ) / 3 );
	$unit = $units[ $power ] ?? 't';

	$abbreviated = $number / pow( 1000, $power );

	return round( $abbreviated, $precision ) . $unit;
}

function text_formatter() {
	return \Voxel\Utils\Text_Formatter\Text_Formatter::get();
}

function link_previewer() {
	return \Voxel\Utils\Link_Previewer\Link_Previewer::get();
}

function get_esm_src( string $handle ): ?string {
	$dist = trailingslashit( get_template_directory_uri() ).'assets/dist/';
	return add_query_arg( 'v', \Voxel\get_assets_version(), $dist.$handle );
}

function minimal_time_diff( int $timestamp, ?int $relative_to = null ): string {
	$relative_to = $relative_to ?? time();
	$diff = abs( $relative_to - $timestamp );

	if ( $diff < 30 ) {
		return _x( 'now', 'time diff', 'voxel' );
	} elseif ( $diff < 60 ) {
		$seconds = $diff;
		return sprintf( _x( '%ds', 'time diff', 'voxel' ), $seconds );
	} elseif ( $diff < 3600 ) {
		$minutes = floor( $diff / 60 );
		return sprintf( _x( '%dm', 'time diff', 'voxel' ), $minutes );
	} elseif ( $diff < 86400 ) {
		$hours = floor( $diff / 3600 );
		return sprintf( _x( '%dh', 'time diff', 'voxel' ), $hours );
	} elseif ( $diff < 604800 ) {
		$days = floor( $diff / 86400 );
		return sprintf( _x( '%dd', 'time diff', 'voxel' ), $days );
	} elseif ( $diff < 53 * 604800 ) {
		$weeks = floor( $diff / 604800 );
		return sprintf( _x( '%dw', 'time diff', 'voxel' ), $weeks );
	} else {
		$years = floor( $diff / ( 365.2425 * 86400 ) );
		return sprintf( _x( '%dy', 'time diff', 'voxel' ), $years );
	}
}

function get_primary_currency(): ?string {
	$payment_service = \Voxel\Product_Types\Payment_Services\Base_Payment_Service::get_active();
	if ( ! $payment_service ) {
		return 'USD';
	}

	return $payment_service->get_primary_currency();
}

function is_test_mode(): bool {
	$payment_service = \Voxel\Product_Types\Payment_Services\Base_Payment_Service::get_active();
	if ( ! $payment_service ) {
		return true;
	}

	return $payment_service->is_test_mode();
}

function use_ecommerce(): void {
	add_filter( 'voxel/use_ecommerce', '__return_true' );
}
