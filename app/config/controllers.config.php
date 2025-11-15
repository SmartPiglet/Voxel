<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

return [
	\Voxel\Controllers\Setup_Controller::class,
	\Voxel\Controllers\Ajax_Controller::class,
	\Voxel\Controllers\Assets_Controller::class,
	\Voxel\Controllers\Cron_Controller::class,
	\Voxel\Controllers\Db_Controller::class,
	\Voxel\Controllers\Event_Controller::class,
	\Voxel\Controllers\Nav_Menus_Controller::class,
	\Voxel\Controllers\Post_Controller::class,
	\Voxel\Controllers\Post_Type_Controller::class,
	\Voxel\Controllers\Post_Types_Controller::class,
	\Voxel\Controllers\Privacy_Controller::class,
	\Voxel\Controllers\Ecommerce\Ecommerce_Controller::class,

	// users
	\Voxel\Controllers\Users\Users_Controller::class,
	\Voxel\Controllers\Users\Single_User_Controller::class,
	\Voxel\Controllers\Users\Profiles_Controller::class,
	\Voxel\Controllers\Users\Role_Controller::class,

	// settings
	\Voxel\Controllers\Settings\Settings_Controller::class,
	\Voxel\Controllers\Settings\Map_Settings_Controller::class,

	// taxonomies
	\Voxel\Controllers\Taxonomies\Taxonomy_Controller::class,
	\Voxel\Controllers\Taxonomies\Term_Controller::class,
	\Voxel\Controllers\Taxonomies\Term_Order_Controller::class,
	\Voxel\Controllers\Taxonomies\Term_Post_Cache_Controller::class,

	// templates
	\Voxel\Controllers\Templates\Templates_Controller::class,
	\Voxel\Controllers\Templates\Base_Templates_Controller::class,
	\Voxel\Controllers\Templates\Custom_Templates_Controller::class,
	\Voxel\Controllers\Templates\Post_Types\Post_Type_Base_Templates_Controller::class,
	\Voxel\Controllers\Templates\Post_Types\Post_Type_Custom_Templates_Controller::class,

	// frontend
	\Voxel\Controllers\Frontend\Auth\Auth_Controller::class,
	\Voxel\Controllers\Frontend\Auth\Google_Controller::class,
	\Voxel\Controllers\Frontend\Auth\Registration_Controller::class,
	\Voxel\Controllers\Frontend\Create_Post\Post_Relations_Controller::class,
	\Voxel\Controllers\Frontend\Create_Post\Submission_Controller::class,
	\Voxel\Controllers\Frontend\Auth\Role_Switch_Controller::class,
	\Voxel\Controllers\Frontend\Search\Quick_Search_Controller::class,
	\Voxel\Controllers\Frontend\Search\Search_Controller::class,
	\Voxel\Controllers\Frontend\Statistics\Statistics_Controller::class,
	\Voxel\Controllers\Frontend\Statistics\Tracking_Controller::class,
	\Voxel\Controllers\Frontend\Statistics\Visits_Chart_Controller::class,
	\Voxel\Controllers\Frontend\Media_Library_Controller::class,
	\Voxel\Controllers\Frontend\Notification_Controller::class,
	\Voxel\Controllers\Frontend\Share_Controller::class,
	\Voxel\Controllers\Frontend\Tabs_Controller::class,
	\Voxel\Controllers\Frontend\User_Controller::class,

	// timeline
	\Voxel\Controllers\Timeline\Timeline_Controller::class,

	// products
	\Voxel\Controllers\Frontend\Products\Cart_Controller::class,
	\Voxel\Controllers\Frontend\Products\Checkout_Controller::class,
	\Voxel\Controllers\Frontend\Products\Direct_Cart_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Orders_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Single_Order_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Modules\Booking_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Modules\Deliverables_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Modules\Promotion_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Modules\Stock_Controller::class,
	\Voxel\Controllers\Frontend\Products\Orders\Modules\Shipping_Controller::class,

	// async actions
	\Voxel\Controllers\Async\Create_Taxonomy_Action::class,
	\Voxel\Controllers\Async\General_Actions::class,
	\Voxel\Controllers\Async\Index_Posts_Action::class,
	\Voxel\Controllers\Async\List_Tax_Rates_Action::class,
	\Voxel\Controllers\Async\List_Shipping_Rates_Action::class,
	\Voxel\Controllers\Async\Purge_Stats_Cache_Action::class,

	// onboarding
	\Voxel\Controllers\Onboarding\Onboarding_Controller::class,
	\Voxel\Controllers\Onboarding\Demo_Import_Controller::class,

	// library
	\Voxel\Controllers\Library\Export_Controller::class,
	\Voxel\Controllers\Library\Import_Controller::class,
	\Voxel\Controllers\Library\Library_Controller::class,

	// compat
	\Voxel\Controllers\Compat\Rank_Math_Controller::class,
	\Voxel\Controllers\Compat\Yoast_Seo_Controller::class,
];
