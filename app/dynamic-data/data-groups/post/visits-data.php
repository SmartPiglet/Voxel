<?php

namespace Voxel\Dynamic_Data\Data_Groups\Post;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Visits_Data {

	protected function get_visit_stats(): Base_Data_Type {
		return Tag::Object('Visit stats')->properties( function() {
			return [
				'views' => Tag::Object('Views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return $this->post->stats->get_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return $this->post->stats->get_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return $this->post->stats->get_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return $this->post->stats->get_views('all');
						} ),
					];
				} ),
				'unique_views' => Tag::Object('Unique views')->properties( function() {
					return [
						'1d' => Tag::Number('Last 24 hours')->render( function() {
							return $this->post->stats->get_unique_views('1d');
						} ),
						'7d' => Tag::Number('Last 7 days')->render( function() {
							return $this->post->stats->get_unique_views('7d');
						} ),
						'30d' => Tag::Number('Last 30 days')->render( function() {
							return $this->post->stats->get_unique_views('30d');
						} ),
						'all' => Tag::Number('All')->render( function() {
							return $this->post->stats->get_unique_views('all');
						} ),
					];
				} ),
				'countries' => Tag::Object_List('Top countries')->items( function() {
					return $this->post->stats->get_tracking_stats('countries');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Country name')->render( function() use ( $item ) {
							if ( ! is_array( $item ) ) {
								return '';
							}

							$list = \Voxel\Utils\Country_List::all();
							return $list[ $item['item'] ?? '' ]['name'] ?? '';
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
						'code' => Tag::String('Country code')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
					];
				} ),
				'ref_domains' => Tag::Object_List('Top referrers (domains)')->items( function() {
					return $this->post->stats->get_tracking_stats('ref_domains');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Domain name')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
						'count' => Tag::Number('Referral count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'ref_urls' => Tag::Object_List('Top referrers (URLs)')->items( function() {
					return $this->post->stats->get_tracking_stats('ref_urls');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('URL')->render( function() use ( $item ) {
							return $item['item'] ?? '';
						} ),
						'count' => Tag::Number('Referral count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'browsers' => Tag::Object_List('Top browsers')->items( function() {
					return $this->post->stats->get_tracking_stats('browsers');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Browser')->render( function() use ( $item ) {
							return \Voxel\Stats\get_browser_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'platforms' => Tag::Object_List('Top platforms')->items( function() {
					return $this->post->stats->get_tracking_stats('platforms');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Platform')->render( function() use ( $item ) {
							return \Voxel\Stats\get_platform_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'devices' => Tag::Object_List('Devices')->items( function() {
					return $this->post->stats->get_tracking_stats('devices');
				} )->properties( function( $index, $item ) {
					return [
						'name' => Tag::String('Device')->render( function() use ( $item ) {
							return \Voxel\Stats\get_device_label( $item['item'] ?? '' );
						} ),
						'count' => Tag::Number('View count')->render( function() use ( $item ) {
							return $item['count'] ?? 0;
						} ),
					];
				} ),
				'last_updated' => Tag::Date('Last updated')->render( function() {
					$time = $this->post->stats->get_last_updated_time();
					if ( $time === null ) {
						return '';
					}

					return date( 'Y-m-d H:i:s', $time );
				} ),
			];
		} );
	}

}
