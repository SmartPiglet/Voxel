<?php

namespace Voxel\Utils;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Sharer {

	public static function get_links() {
		return apply_filters( 'voxel/share-links', [
			'facebook' => [
				'label' => 'Facebook',
				'icon' => function() {
					return \Voxel\get_svg('facebook-fill.svg');
				},
				'link' => function( $details ) {
					return add_query_arg( 'u', $details['link'], 'https://www.facebook.com/sharer/sharer.php' );
				},
			],
			'twitter' => [
				'label' => 'X',
				'icon' => function() {
					return \Voxel\get_svg('x.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( [
						'url' => $details['link'],
						'text' => $details['title'],
					], 'https://x.com/intent/tweet' );
				},
			],
			'linkedin' => [
				'label' => 'LinkedIn',
				'icon' => function() {
					return \Voxel\get_svg('linkedin-original.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( [
						'url' => $details['link'],
					], 'https://www.linkedin.com/sharing/share-offsite' );
				},
			],
			'reddit' => [
				'label' => 'Reddit',
				'icon' => function() {
					return \Voxel\get_svg('reddit.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( [
						'url' => $details['link'],
						'title' => $details['title'],
					], 'https://www.reddit.com/submit' );
				},
			],
			'tumblr' => [
				'label' => 'Tumblr',
				'icon' => function() {
					return \Voxel\get_svg('tumblr.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( [
						'canonicalUrl' => $details['link'],
						'title' => $details['title'],
					], 'https://www.tumblr.com/widgets/share/tool' );
				},
			],
			'whatsapp' => [
				'label' => 'WhatsApp',
				'icon' => function() {
					return \Voxel\get_svg('whatsapp.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( 'text', $details['title'].' '.$details['link'], 'https://api.whatsapp.com/send' );
				},
			],
			'telegram' => [
				'label' => 'Telegram',
				'icon' => function() {
					return \Voxel\get_svg('telegram-original.svg');
				},
				'link' =>  function( $details ) {
					return add_query_arg( [
						'url' => $details['link'],
						'text' => $details['title'],
					], 'https://t.me/share/url' );
				},
			],
			'copy-link' => [
				'label' => 'Copy link',
				'icon' => function() {
					return \Voxel\get_svg('link-alt.svg');
				},
				'link' => function() {
					return '#';
				},
			],
			'native-share' => [
				'label' => 'Share via...',
				'icon' => function() {
					return \Voxel\get_svg('share.svg');
				},
				'link' => function() {
					return '#';
				},
			],
		] );
	}

	public static function get_default_config() {
		return [
			[
				'type' => 'facebook',
				'key' => 'facebook',
				'label' => 'Facebook',
			],
			[
				'type' => 'twitter',
				'key' => 'twitter',
				'label' => 'X',
			],
			[
				'type' => 'linkedin',
				'key' => 'linkedin',
				'label' => 'LinkedIn',
			],
			[
				'type' => 'reddit',
				'key' => 'reddit',
				'label' => 'Reddit',
			],
			[
				'type' => 'tumblr',
				'key' => 'tumblr',
				'label' => 'Tumblr',
			],
			[
				'type' => 'whatsapp',
				'key' => 'whatsapp',
				'label' => 'WhatsApp',
			],
			[
				'type' => 'telegram',
				'key' => 'telegram',
				'label' => 'Telegram',
			],
			[
				'type' => 'copy-link',
				'key' => 'copy-link',
				'label' => 'Copy link',
			],
			[
				'type' => 'native-share',
				'key' => 'native-share',
				'label' => 'Share via...',
			],
		];
	}

	public static function get_google_calendar_link( $args ) {
		$args = array_merge( [
			'start' => '',
			'end' => '',
			'title' => '',
			'description' => '',
			'location' => '',
			'timezone' => '',
		], $args );

		$start = strtotime( $args['start'] );
		$end = strtotime( $args['end'] );

		if ( ! $start ) {
			return null;
		}

		if ( ! ( $end && $end >= $start ) ) {
			$end = $start;
		}

		return add_query_arg( [
			'text' => $args['title'],
			'dates' => sprintf( '%s/%s', date( 'Ymd\THis', $start ), date( 'Ymd\THis', $end ) ),
			'details' => wp_kses( $args['description'], [] ),
			'location' => $args['location'],
			'ctz' => $args['timezone'],
		], 'https://calendar.google.com/calendar/render?action=TEMPLATE&trp=true' );
	}

	public static function get_icalendar_data( $args ) {
		$args = array_merge( [
			'start' => '',
			'end' => '',
			'title' => '',
			'description' => '',
			'location' => '',
			'url' => '',
		], $args );

		$start = strtotime( $args['start'] );
		$end = strtotime( $args['end'] );

		if ( ! $start ) {
			return null;
		}

		if ( ! ( $end && $end >= $start ) ) {
			$end = $start;
		}

		$start = date( 'Ymd\THis', $start );
		$end = date( 'Ymd\THis', $end );
		$title = sanitize_text_field( $args['title'] );
		$description = str_replace(
			["\r\n", "\r", "\n", "&nbsp;"],
			[ "\\n", "\\n", "\\n", '' ],
			sanitize_textarea_field( wp_kses( $args['description'], [] ) )
		);
		$location = sanitize_text_field( $args['location'] );
		$url = sanitize_text_field( $args['url'] );

		$dtstamp = \Voxel\now()->format( 'Ymd\THis' );
		$uid = sprintf( '%s?ics_uid=%s', home_url('/'), md5( wp_json_encode( [ $title, $start, $end, $url ] ) ) );

		return <<<TXT
		BEGIN:VCALENDAR
		VERSION:2.0
		PRODID:-//hacksw/handcal//NONSGML v1.0//EN
		CALSCALE:GREGORIAN
		BEGIN:VEVENT
		LOCATION:{$location}
		DESCRIPTION:{$description}
		DTSTART:{$start}
		DTEND:{$end}
		SUMMARY:{$title}
		URL;VALUE=URI:{$url}
		DTSTAMP:{$dtstamp}
		UID:{$uid}
		END:VEVENT
		END:VCALENDAR
		TXT;
	}
}
