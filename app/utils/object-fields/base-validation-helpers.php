<?php

namespace Voxel\Utils\Object_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Base_Validation_Helpers {

	protected function validate_is_empty( $value ) {
		// required field check, handling 0, '0', and 0.0 as special cases
		if ( $this->is_required() && $this->is_empty( $value ) ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name is required', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
				]
			) );
		}
	}

	protected function validate_minlength( $value, $strip_tags = false ) {
		if ( $strip_tags ) {
			$value = wp_strip_all_tags( $value );
		}

		if ( is_numeric( $this->get_prop('minlength') ) && mb_strlen( $value ) < $this->get_prop('minlength') ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name can\'t be shorter than @length characters', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@length' => absint( $this->get_prop('minlength') ),
				]
			) );
		}
	}

	protected function validate_maxlength( $value, $strip_tags = false ) {
		if ( $strip_tags ) {
			$value = wp_strip_all_tags( $value );
		}

		if ( is_numeric( $this->get_prop('maxlength') ) && mb_strlen( $value ) > $this->get_prop('maxlength') ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name can\'t be longer than @length characters', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@length' => absint( $this->get_prop('maxlength') ),
				]
			) );
		}
	}

	protected function validate_is_numeric( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name is not a valid number', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
				]
			) );
		}
	}

	protected function validate_email( $value ) {
		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name must be a valid email address', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
				]
			) );
		}
	}
}
