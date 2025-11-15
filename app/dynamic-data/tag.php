<?php

namespace Voxel\Dynamic_Data;

use \Voxel\Dynamic_Data\Data_Types as Data_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Tag {

	public static function String() {
		return new Data_Types\Data_String( ...func_get_args() );
	}

	public static function Number() {
		return new Data_Types\Data_Number( ...func_get_args() );
	}

	public static function Bool() {
		return new Data_Types\Data_Bool( ...func_get_args() );
	}

	public static function Date() {
		return new Data_Types\Data_Date( ...func_get_args() );
	}

	public static function Email() {
		return new Data_Types\Data_Email( ...func_get_args() );
	}

	public static function URL() {
		return new Data_Types\Data_URL( ...func_get_args() );
	}

	public static function Object() {
		return new Data_Types\Data_Object( ...func_get_args() );
	}

	public static function Object_List() {
		return new Data_Types\Data_Object_List( ...func_get_args() );
	}

}
