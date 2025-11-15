<?php

namespace Voxel\Dynamic_Data\Data_Groups\Term;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'term';
	}

	protected static $instances = [];
	public static function get( \Voxel\Term $term ): self {
		if ( ! array_key_exists( $term->get_id(), static::$instances ) ) {
			static::$instances[ $term->get_id() ] = new static( $term );
		}

		return static::$instances[ $term->get_id() ];
	}

	public $term;
	protected function __construct( \Voxel\Term $term ) {
		$this->term = $term;
	}

	public function get_term(): \Voxel\Term {
		return $this->term;
	}

	protected function properties(): array {
		return [
			'id' => Tag::Number('ID')->render( function() {
				return $this->term->get_id() ?: '';
			} ),
			'label' => Tag::String('Label')->render( function() {
				return $this->term->get_label();
			} ),
			'slug' => Tag::String('Slug')->render( function() {
				return $this->term->get_slug();
			} ),
			'description' => Tag::String('Description')->render( function() {
				$content = $this->term->get_description();
				$content = links_add_target( make_clickable( $content ) );
				$content = wpautop( $content );
				return $content;
			} ),
			'icon' => Tag::String('Icon')->render( function() {
				return $this->term->get_icon();
			} ),
			'link' => Tag::URL('Permalink')->render( function() {
				return $this->term->get_link();
			} ),
			'image' => Tag::Number('Image')->render( function() {
				return $this->term->get_image_id();
			} ),
			'color' => Tag::String('Color')->render( function() {
				return get_term_meta( $this->term->get_id(), 'voxel_color', true );
			} ),
			'area' => Tag::Object('Area')->properties( function() {
				return [
					'address' => Tag::String('Address')->render( function() {
						return $this->term->get_area()['address'];
					} ),
					'southwest' => Tag::Object('Southwest')->properties( function() {
						return [
							'lat' => Tag::Number('Latitude')->render( function() {
								return $this->term->get_area()['swlat'];
							} ),
							'lng' => Tag::Number('Longitude')->render( function() {
								return $this->term->get_area()['swlng'];
							} ),
						];
					} ),
					'northeast' => Tag::Object('Northeast')->properties( function() {
						return [
							'lat' => Tag::Number('Latitude')->render( function() {
								return $this->term->get_area()['nelat'];
							} ),
							'lng' => Tag::Number('Longitude')->render( function() {
								return $this->term->get_area()['nelng'];
							} ),
						];
					} ),
				];
			} ),
			'parent' => Tag::Object('Parent term')->properties( function() {
				$parent = $this->term->get_parent();
				if ( $parent === null ) {
					return Term_Data_Group::mock();
				}

				return \Voxel\Dynamic_Data\Group::Term( $parent );
			} ),
		];
	}

	protected function aliases(): array {
		return [
			':id' => 'id',
			':label' => 'label',
			':slug' => 'slug',
			':description' => 'description',
			':icon' => 'icon',
			':url' => 'link',
			':image' => 'image',
			':color' => 'color',
			':area' => 'area',
			'name' => 'label',
		];
	}

	public function methods(): array {
		return [
			'meta' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\Term_Meta_Method::class,
			'post_count' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\Term_Post_Count_Method::class,
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Term::dummy() );
	}
}
