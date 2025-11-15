<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Field extends Base_Post_Field {

	protected $supported_conditions = ['date'];

	protected $props = [
		'type' => 'date',
		'label' => 'Date',
		'placeholder' => '',
		'enable_timepicker' => false,
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'enable_timepicker' => [
				'type' => Form_Models\Switcher_Model::class,
				'label' => 'Enable timepicker',
				'description' => 'Set whether users can also select the time of day when adding a date.',
				'classes' => 'x-col-12',
			],
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model( [
				'placeholder' => 'Enter a date or datetime string e.g. 2026-01-01 09:00:00',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		$timestamp = strtotime( $value['date'] ?? '' );
		if ( ! $timestamp ) {
			return null;
		}

		if ( $this->props['enable_timepicker'] && ( $time = strtotime( $value['time'] ?? '' ) ) ) {
			$timestamp += 60 * ( ( absint( date( 'H', $time ) ) * 60 ) + absint( date( 'i', $time ) ) );
		}

		$format = $this->props['enable_timepicker'] ? 'Y-m-d H:i:s' : 'Y-m-d';
		return date( $format, $timestamp );
	}

	public function update( $value ): void {
		if ( empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), $value );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_value = $this->render_default_value( $this->get_prop('default') );
			$timestamp = strtotime( $default_value ?? '' );

			return [
				'date' => $timestamp ? date( 'Y-m-d', $timestamp ) : null,
				'time' => $timestamp ? date( 'H:i', $timestamp ) : null,
			];
		} else {
			$value = $this->get_value();
			$timestamp = strtotime( $value ?? '' );

			return [
				'date' => $timestamp ? date( 'Y-m-d', $timestamp ) : null,
				'time' => $timestamp ? date( 'H:i', $timestamp ) : null,
			];
		}
	}

	public function get_required_scripts(): array {
		return [ 'pikaday' ];
	}

	protected function frontend_props() {
		wp_enqueue_style( 'pikaday' );

		return [
			'enable_timepicker' => $this->props['enable_timepicker'],
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
		];
	}

	public function dynamic_data() {
		return Tag::Object( $this->get_label() )->properties( function() {
			return [
				'date' => Tag::Date('Date')->render( function() {
					return $this->get_value();
				} ),
				'is_finished' => Tag::Bool('Is finished?')->render( function() {
					$value = $this->get_value();
					$timestamp = strtotime( $value ?? '' );
					if ( empty( $value ) || ! $timestamp ) {
						return null;
					}

					$date = new \DateTime( date( 'Y-m-d H:i:s', $timestamp ), $this->post->get_timezone() );
					$now = new \DateTime( 'now', $this->post->get_timezone() );

					return $date < $now;
				} ),
			];
		} )->render( function() {
			// backwards compatibility
			return $this->get_value();
		} );
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}
}
