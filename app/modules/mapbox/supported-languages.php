<?php

namespace Voxel\Modules\Mapbox;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Supported_Languages {

	// @link https://docs.mapbox.com/api/search/geocoding/#language-coverage
	public static function global_coverage() {
		return [
			'de' => 'German',
			'en' => 'English',
			'es' => 'Spanish',
			'fr' => 'French',
			'it' => 'Italian',
			'nl' => 'Dutch',
			'pl' => 'Polish',
		];
	}

	public static function local_coverage() {
		return [
			'az' => 'Azerbaijani',
			'bn' => 'Bengali',
			'ca' => 'Catalan',
			'cs' => 'Czech',
			'da' => 'Danish',
			'el' => 'Modern Greek',
			'et' => 'Estonian',
			'fa' => 'Persian',
			'fi' => 'Finnish',
			'ga' => 'Irish',
			'hu' => 'Hungarian',
			'id' => 'Indonesian',
			'is' => 'Icelandic',
			'ja' => 'Japanese',
			'ka' => 'Georgian',
			'km' => 'Central Khmer',
			'ko' => 'Korean',
			'lt' => 'Lithuanian',
			'lv' => 'Latvian',
			'mk' => 'Macedonian',
			'mn' => 'Mongolian',
			'ms' => 'Malay macrolanguage',
			'nb' => 'Norwegian Bokmål',
			'pt' => 'Portuguese',
			'ro' => 'Romanian',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'sq' => 'Albanian',
			'th' => 'Thai',
			'tl' => 'Tagalog',
			'uk' => 'Ukrainian',
			'vi' => 'Vietnamese',
			'zh' => 'Chinese',
			'zh_Hans' => 'Simplified Chinese',
			'zh_Hant' => 'Traditional Chinese',
			'zh_TW' => 'Taiwanese Mandarin',
		];
	}

	public static function limited_coverage() {
		return [
			'ar' => 'Arabic',
			'bs' => 'Bosnian',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'kk' => 'Kazakh',
			'lo' => 'Lao',
			'my' => 'Burmese',
			'ru' => 'Russian',
			'sr' => 'Serbian',
			'sv' => 'Swedish',
			'te' => 'Telugu',
			'tk' => 'Turkmen',
			'tr' => 'Turkish',
		];
	}
}
