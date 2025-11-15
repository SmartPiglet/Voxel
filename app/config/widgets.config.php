<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

return [
	\Voxel\Widgets\Search_Form::class,
	\Voxel\Widgets\Navbar::class,
	\Voxel\Widgets\User_Bar::class,
	\Voxel\get( 'settings.timeline.enabled', true ) ? \Voxel\Widgets\Timeline::class : null,
	\Voxel\Widgets\Gallery::class,
	\Voxel\Widgets\Slider::class,
	\Voxel\Widgets\Advanced_list::class,
	\Voxel\Widgets\Work_hours::class,
	\Voxel\Widgets\Review_Stats::class,
	\Voxel\Widgets\Create_Post::class,
	\Voxel\Widgets\Ring_Chart::class,
	\Voxel\Widgets\Post_Feed::class,
	\Voxel\Widgets\Quick_Search::class,
	\Voxel\Widgets\Login::class,
	\Voxel\Widgets\Product_Form::class,
	\Voxel\Widgets\Orders::class,
	\Voxel\Widgets\Map::class,
	\Voxel\Widgets\Print_Template::class,
	\Voxel\Widgets\Popup_Kit::class,
	\Voxel\Widgets\Timeline_Kit::class,
	// \Voxel\Widgets\Booking_Calendar::class,
	// \Voxel\Widgets\QR_Tag_Handler::class,
	\Voxel\Widgets\Bar_Chart::class,
	\Voxel\Widgets\Template_Tabs::class,
	\Voxel\Widgets\Countdown::class,
	\Voxel\Widgets\Term_Feed::class,
	\Voxel\Widgets\Visits_Chart::class,
	\Voxel\Widgets\Nested_Accordion::class,
	\Voxel\Widgets\Nested_Tabs::class,
	\Voxel\Widgets\Cart_Summary::class,
	\Voxel\Widgets\Product_Price::class,
	\Voxel\Widgets\Image::class,
];
