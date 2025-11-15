<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Schema {

	public static function Object() {
		return new Data_Object( ...func_get_args() );
	}

	public static function Bool() {
		return new Data_Bool( ...func_get_args() );
	}

	public static function Enum() {
		return new Data_Enum( ...func_get_args() );
	}

	public static function Float() {
		return new Data_Float( ...func_get_args() );
	}

	public static function Int() {
		return new Data_Int( ...func_get_args() );
	}

	public static function Date() {
		return new Data_Date( ...func_get_args() );
	}

	public static function List() {
		return new Data_List( ...func_get_args() );
	}

	public static function Keyed_List() {
		return new Data_Keyed_List( ...func_get_args() );
	}

	public static function Object_List() {
		return new Data_Object_List( ...func_get_args() );
	}

	public static function Keyed_Object_List() {
		return new Data_Keyed_Object_List( ...func_get_args() );
	}

	public static function String() {
		return new Data_String( ...func_get_args() );
	}

	public static function Const() {
		return new Data_Const( ...func_get_args() );
	}

	public static function optimize_for_storage( array $exported_config ) {
		foreach ( $exported_config as $key => $value ) {
			if ( is_array( $value ) ) {
				$exported_config[ $key ] = static::optimize_for_storage( $value );
			}

			if ( $value === null || $value === '' || $value === [] ) {
				unset( $exported_config[ $key ] );
			}
		}

		return $exported_config;
	}
}
