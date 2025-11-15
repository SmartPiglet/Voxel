<?php

namespace Voxel;

use \Voxel\Product_Types\{
	Additions,
	Product_Fields,
	Product_Addons,
	Product_Attributes,
	Data_Inputs,
};

if ( ! defined('ABSPATH') ) {
	exit;
}

return [
	'product_addons' => apply_filters( 'voxel/product-types/product-addons', [
		'custom-select' => Product_Addons\Custom_Select_Addon::class,
		'custom-multiselect' => Product_Addons\Custom_Multiselect_Addon::class,
		'select' => Product_Addons\Select_Addon::class,
		'multiselect' => Product_Addons\Multiselect_Addon::class,
		'numeric' => Product_Addons\Numeric_Addon::class,
		'switcher' => Product_Addons\Switcher_Addon::class,
	] ),

	'product_fields' => apply_filters( 'voxel/product-types/product-fields', [
		'base-price' => Product_Fields\Base_Price_Field::class,
		'subscription-interval' => Product_Fields\Subscription_Interval_Field::class,
		'deliverables' => Product_Fields\Deliverables_Field::class,
		'booking' => Product_Fields\Booking_Field::class,
		'addons' => Product_Fields\Addons_Field::class,
		'variations' => Product_Fields\Variations_Field::class,
		'custom-prices' => Product_Fields\Custom_Prices_Field::class,
		'stock' => Product_Fields\Stock_Field::class,
		'shipping' => Product_Fields\Shipping_Field::class,
		'currency' => Product_Fields\Currency_Field::class,
	] ),

	'data_inputs' => apply_filters( 'voxel/product-types/data-inputs', [
		'text' => Data_Inputs\Text_Data_Input::class,
		'textarea' => Data_Inputs\Textarea_Data_Input::class,
		'number' => Data_Inputs\Number_Data_Input::class,
		'select' => Data_Inputs\Select_Data_Input::class,
		'multiselect' => Data_Inputs\Multiselect_Data_Input::class,
		'email' => Data_Inputs\Email_Data_Input::class,
		'phone' => Data_Inputs\Phone_Data_Input::class,
		'switcher' => Data_Inputs\Switcher_Data_Input::class,
		'url' => Data_Inputs\Url_Data_Input::class,
		// 'date' => Data_Inputs\Date_Data_Input::class,
	] ),
];
