<?php

namespace Voxel\Utils;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Currency_List {

	private static $list = [
		'USD' => 'USD - US Dollar',
		'EUR' => 'EUR - Euro',
		'AED' => 'AED - United Arab Emirates Dirham',
		'AFN' => 'AFN - Afghan Afghani',
		'ALL' => 'ALL - Albanian Lek',
		'AMD' => 'AMD - Armenian Dram',
		'ANG' => 'ANG - Netherlands Antillean Guilder',
		'AOA' => 'AOA - Angolan Kwanza',
		'ARS' => 'ARS - Argentine Peso',
		'AUD' => 'AUD - Australian Dollar',
		'AWG' => 'AWG - Aruban Florin',
		'AZN' => 'AZN - Azerbaijani Manat',
		'BAM' => 'BAM - Bosnia-Herzegovina Convertible Mark',
		'BBD' => 'BBD - Barbadian Dollar',
		'BDT' => 'BDT - Bangladeshi Taka',
		'BGN' => 'BGN - Bulgarian Lev',
		'BIF' => 'BIF - Burundian Franc',
		'BMD' => 'BMD - Bermudan Dollar',
		'BND' => 'BND - Brunei Dollar',
		'BOB' => 'BOB - Bolivian Boliviano',
		'BRL' => 'BRL - Brazilian Real',
		'BSD' => 'BSD - Bahamian Dollar',
		'BWP' => 'BWP - Botswanan Pula',
		'BZD' => 'BZD - Belize Dollar',
		'CAD' => 'CAD - Canadian Dollar',
		'CDF' => 'CDF - Congolese Franc',
		'CHF' => 'CHF - Swiss Franc',
		'CLP' => 'CLP - Chilean Peso',
		'CNY' => 'CNY - Chinese Yuan',
		'COP' => 'COP - Colombian Peso',
		'CRC' => 'CRC - Costa Rican Colón',
		'CVE' => 'CVE - Cape Verdean Escudo',
		'CZK' => 'CZK - Czech Koruna',
		'DJF' => 'DJF - Djiboutian Franc',
		'DKK' => 'DKK - Danish Krone',
		'DOP' => 'DOP - Dominican Peso',
		'DZD' => 'DZD - Algerian Dinar',
		'EGP' => 'EGP - Egyptian Pound',
		'ETB' => 'ETB - Ethiopian Birr',
		'FJD' => 'FJD - Fijian Dollar',
		'FKP' => 'FKP - Falkland Islands Pound',
		'GBP' => 'GBP - British Pound',
		'GEL' => 'GEL - Georgian Lari',
		'GIP' => 'GIP - Gibraltar Pound',
		'GMD' => 'GMD - Gambian Dalasi',
		'GNF' => 'GNF - Guinean Franc',
		'GTQ' => 'GTQ - Guatemalan Quetzal',
		'GYD' => 'GYD - Guyanaese Dollar',
		'HKD' => 'HKD - Hong Kong Dollar',
		'HNL' => 'HNL - Honduran Lempira',
		'HRK' => 'HRK - Croatian Kuna',
		'HTG' => 'HTG - Haitian Gourde',
		'HUF' => 'HUF - Hungarian Forint',
		'IDR' => 'IDR - Indonesian Rupiah',
		'ILS' => 'ILS - Israeli New Shekel',
		'INR' => 'INR - Indian Rupee',
		'ISK' => 'ISK - Icelandic Króna',
		'JMD' => 'JMD - Jamaican Dollar',
		'JPY' => 'JPY - Japanese Yen',
		'KES' => 'KES - Kenyan Shilling',
		'KGS' => 'KGS - Kyrgystani Som',
		'KHR' => 'KHR - Cambodian Riel',
		'KMF' => 'KMF - Comorian Franc',
		'KRW' => 'KRW - South Korean Won',
		'KYD' => 'KYD - Cayman Islands Dollar',
		'KZT' => 'KZT - Kazakhstani Tenge',
		'LAK' => 'LAK - Laotian Kip',
		'LBP' => 'LBP - Lebanese Pound',
		'LKR' => 'LKR - Sri Lankan Rupee',
		'LRD' => 'LRD - Liberian Dollar',
		'LSL' => 'LSL - Lesotho Loti',
		'MAD' => 'MAD - Moroccan Dirham',
		'MDL' => 'MDL - Moldovan Leu',
		'MGA' => 'MGA - Malagasy Ariary',
		'MKD' => 'MKD - Macedonian Denar',
		'MMK' => 'MMK - Myanmar Kyat',
		'MNT' => 'MNT - Mongolian Tugrik',
		'MOP' => 'MOP - Macanese Pataca',
		'MRO' => 'MRO - Mauritanian Ouguiya (1973–2017)',
		'MUR' => 'MUR - Mauritian Rupee',
		'MVR' => 'MVR - Maldivian Rufiyaa',
		'MWK' => 'MWK - Malawian Kwacha',
		'MXN' => 'MXN - Mexican Peso',
		'MYR' => 'MYR - Malaysian Ringgit',
		'MZN' => 'MZN - Mozambican Metical',
		'NAD' => 'NAD - Namibian Dollar',
		'NGN' => 'NGN - Nigerian Naira',
		'NIO' => 'NIO - Nicaraguan Córdoba',
		'NOK' => 'NOK - Norwegian Krone',
		'NPR' => 'NPR - Nepalese Rupee',
		'NZD' => 'NZD - New Zealand Dollar',
		'OMR' => 'OMR - Omani Rial',
		'PAB' => 'PAB - Panamanian Balboa',
		'PEN' => 'PEN - Peruvian Sol',
		'PGK' => 'PGK - Papua New Guinean Kina',
		'PHP' => 'PHP - Philippine Piso',
		'PKR' => 'PKR - Pakistani Rupee',
		'PLN' => 'PLN - Polish Zloty',
		'PYG' => 'PYG - Paraguayan Guarani',
		'QAR' => 'QAR - Qatari Rial',
		'RON' => 'RON - Romanian Leu',
		'RSD' => 'RSD - Serbian Dinar',
		'RUB' => 'RUB - Russian Ruble',
		'RWF' => 'RWF - Rwandan Franc',
		'SAR' => 'SAR - Saudi Riyal',
		'SBD' => 'SBD - Solomon Islands Dollar',
		'SCR' => 'SCR - Seychellois Rupee',
		'SEK' => 'SEK - Swedish Krona',
		'SGD' => 'SGD - Singapore Dollar',
		'SHP' => 'SHP - St. Helena Pound',
		'SLL' => 'SLL - Sierra Leonean Leone',
		'SOS' => 'SOS - Somali Shilling',
		'SRD' => 'SRD - Surinamese Dollar',
		'STD' => 'STD - São Tomé & Príncipe Dobra (1977–2017)',
		'SZL' => 'SZL - Swazi Lilangeni',
		'THB' => 'THB - Thai Baht',
		'TJS' => 'TJS - Tajikistani Somoni',
		'TOP' => 'TOP - Tongan Paʻanga',
		'TRY' => 'TRY - Turkish Lira',
		'TTD' => 'TTD - Trinidad & Tobago Dollar',
		'TWD' => 'TWD - New Taiwan Dollar',
		'TZS' => 'TZS - Tanzanian Shilling',
		'UAH' => 'UAH - Ukrainian Hryvnia',
		'UGX' => 'UGX - Ugandan Shilling',
		'UYU' => 'UYU - Uruguayan Peso',
		'UZS' => 'UZS - Uzbekistani Som',
		'VND' => 'VND - Vietnamese Dong',
		'VUV' => 'VUV - Vanuatu Vatu',
		'WST' => 'WST - Samoan Tala',
		'XAF' => 'XAF - Central African CFA Franc',
		'XCD' => 'XCD - East Caribbean Dollar',
		'XOF' => 'XOF - West African CFA Franc',
		'XPF' => 'XPF - CFP Franc',
		'YER' => 'YER - Yemeni Rial',
		'ZAR' => 'ZAR - South African Rand',
		'ZMW' => 'ZMW - Zambian Kwacha',
	];

	private static $zeroDecimal = [
		'BIF' => true,
		'CLP' => true,
		'DJF' => true,
		'GNF' => true,
		'JPY' => true,
		'KMF' => true,
		'KRW' => true,
		'MGA' => true,
		'PYG' => true,
		'RWF' => true,
		'UGX' => true,
		'VND' => true,
		'VUV' => true,
		'XAF' => true,
		'XOF' => true,
		'XPF' => true,
	];

	public static function all() {
		return static::$list;
	}

	public static function is_zero_decimal( $currency ) {
		return isset( static::$zeroDecimal[ strtoupper( (string) $currency ) ] );
	}

	public static function exists( string $currency ) {
		return isset( static::$list[ strtoupper( $currency ) ] );
	}

	public static function only( array $currency_codes ): array {
		$result = [];
		foreach ( $currency_codes as $code ) {
			$code = strtoupper( (string) $code );
			if ( isset( static::$list[ $code ] ) ) {
				$result[ $code ] = static::$list[ $code ];
			}
		}

		return $result;
	}
}
