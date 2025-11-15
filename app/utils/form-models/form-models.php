<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Models {

	public static function Text() {
		return new Text_Model( ...func_get_args() );
	}

	public static function Textarea() {
		return new Textarea_Model( ...func_get_args() );
	}

	public static function Checkboxes() {
		return new Checkboxes_Model( ...func_get_args() );
	}

	public static function Color() {
		return new Color_Model( ...func_get_args() );
	}

	public static function Dtag() {
		return new Dtag_Model( ...func_get_args() );
	}

	public static function Icon() {
		return new Icon_Model( ...func_get_args() );
	}

	public static function Info() {
		return new Info_Model( ...func_get_args() );
	}

	public static function Key() {
		return new Key_Model( ...func_get_args() );
	}

	public static function Media() {
		return new Media_Model( ...func_get_args() );
	}

	public static function Number() {
		return new Number_Model( ...func_get_args() );
	}

	public static function Password() {
		return new Password_Model( ...func_get_args() );
	}

	public static function Radio() {
		return new Radio_Model( ...func_get_args() );
	}

	public static function Select() {
		return new Select_Model( ...func_get_args() );
	}

	public static function Switcher() {
		return new Switcher_Model( ...func_get_args() );
	}

	public static function Taxonomy_Select() {
		return new Taxonomy_Select_Model( ...func_get_args() );
	}

	public static function Raw() {
		return new Raw_Model( ...func_get_args() );
	}
}
