<?php

namespace Voxel\Product_Types\Product_Attributes;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Custom_Attribute extends Base_Attribute {

	protected $props = [
		'type' => 'custom',
		'key' => null,
		'label' => null,
		'choices' => [],
		'display_mode' => 'dropdown',
	];

	public function get_choices(): array {
		$choices = [];
		foreach ( (array) $this->props['choices'] as $id => $choice ) {
			if ( ! is_array( $choice ) || empty( $choice['label'] ) ) {
				continue;
			}

			$data = [
				'label' => (string) $choice['label'],
				'value' => $id,
			];

			if ( $this->props['display_mode'] === 'colors' ) {
				$data['color'] = $choice['color'] ?? null;
			}

			if ( $this->props['display_mode'] === 'cards' ) {
				$data['subheading'] = $choice['subheading'] ?? null;
			}

			if ( $this->props['display_mode'] === 'cards' ||  $this->props['display_mode'] === 'images' ) {
				$data['image'] = $choice['image'] ?? null;
			}

			$choices[ $id ] = $data;
		}

		return $choices;
	}

	public function get_product_form_frontend_config() {
		$choices = $this->get_choices();
		$frontend_choices = [];
		foreach ( $choices as $key => $choice ) {
			if ( $this->props['display_mode'] === 'cards' ||  $this->props['display_mode'] === 'images' ) {
				$choices[ $key ]['image'] = null;

				if ( $image_url = wp_get_attachment_image_url( $choice['image'], 'medium' ) ) {
					$choices[ $key ]['image'] = [
						'url' => $image_url,
						'alt' => get_post_meta( $choice['image'], '_wp_attachment_image_alt', true ),
					];
				}
			}

			$frontend_choices[ 'choice_'.$key ] = $choices[ $key ];
		}

		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'props' => [
				'display_mode' => $this->props['display_mode'],
				'choices' => (object) $frontend_choices,
			],
		];
	}
}
