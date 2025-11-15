<?php

namespace Voxel\Post_Types\Fields\Singular;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Description_Field extends \Voxel\Post_Types\Fields\Texteditor_Field {

	protected $supported_conditions = ['text'];

	public function before_props_assigned(): void {
		$this->props['label'] = 'Description';
		$this->props['type'] = 'description';
		$this->props['key'] = 'description';
		$this->props['editor-type'] = 'wp-editor-basic';
	}

	public function sanitize( $value ) {
		if ( $this->get_model_value('editor-type') === 'plain-text' ) {
			return sanitize_textarea_field( trim( $value ) );
		}

		$content = wp_kses_post( trim( $value ) );
		$content = preg_replace( '/(<(.*)>)?<!-- [\/]?wp:(.*) --\>(<\/(.*)>)?[\\n]?/', '', $content );
		return $content;
	}

	public function update( $value ): void {
		global $wpdb;
		$wpdb->update( $wpdb->posts, [
			'post_content' => $value,
		], $where = [ 'ID' => $this->post->get_id() ] );
	}

	public function get_value() {
		return $this->post->get_content();
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			return $this->render_default_value( $this->get_prop('default') );
		} else {
			if ( $this->get_model_value('editor-type') === 'plain-text' ) {
				$content = $this->get_value();

				// prevent "more" tag from getting replaced with markup in the Create post (VX) JSON settings
				$content = str_replace( '<!--more-->', '', $content );

				return $content;
			} else {
				$content = (string) $this->get_value();
				$content = preg_replace( '/(<(.*)>)?<!-- [\/]?wp:(.*) --\>(<\/(.*)>)?[\\n]?/', '', $content );

				// prevent "more" tag from getting replaced with markup in the Create post (VX) JSON settings
				$content = str_replace( '<!--more-->', '<!--_more-->', $content );
				$content = wpautop( $content );

				return $content;
			}
		}
	}

	public static function is_singular(): bool {
		return true;
	}
}
