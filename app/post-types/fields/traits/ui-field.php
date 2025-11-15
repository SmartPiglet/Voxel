<?php

namespace Voxel\Post_Types\Fields\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Ui_Field {

	public function is_ui() {
		return true;
	}

	public function sanitize( $value ) {
		//
	}

	public function validate( $value ): void {
		//
	}

	public function update( $value ): void {
		//
	}

}
