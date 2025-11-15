<?php

namespace Voxel\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Share_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_share.get_networks', '@get_networks' );
		$this->on( 'voxel_ajax_nopriv_share.get_networks', '@get_networks' );
	}

	protected function get_networks() {
		$data = [];
		$data['link'] = sanitize_url( wp_unslash( $_GET['link'] ?? '' ) );
		$data['title'] = sanitize_text_field( wp_unslash( $_GET['title'] ?? '' ) );
		$presets = \Voxel\Utils\Sharer::get_links();

		$networks = (array) \Voxel\get( 'settings.share.networks' );
		if ( empty( $networks ) ) {
			$networks = \Voxel\Utils\Sharer::get_default_config();
		}

		$items = [];
		foreach ( $networks as $network ) {
			if ( ( $network['type'] ?? null ) === 'ui-heading' ) {
				// $items[] = [
				// 	'type' => 'ui-heading',
				// 	'label' => $network['label'] ?? '',
				// ];
				continue;
			}

			$preset = $presets[ $network['type'] ?? '' ] ?? null;
			if ( $preset ) {
				$items[] = [
					'type' => $network['type'],
					'label' => $network['label'] ?? '',
					'icon' => \Voxel\get_icon_markup( $network['icon'] ?? '' ) ?: $preset['icon'](),
					'link' => $preset['link']( $data ),
				];
			}
		}

		return wp_send_json( [
			'success' => true,
			'data' => $items,
		] );
	}
}
