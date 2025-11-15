<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

return [
	'field_types' => apply_filters( 'voxel/field-types', [
		'title' => \Voxel\Post_Types\Fields\Singular\Title_Field::class,
		'description' => \Voxel\Post_Types\Fields\Singular\Description_Field::class,
		'timezone' => \Voxel\Post_Types\Fields\Singular\Timezone_Field::class,
		'text' => \Voxel\Post_Types\Fields\Text_Field::class,
		'number' => \Voxel\Post_Types\Fields\Number_Field::class,
		'switcher' => \Voxel\Post_Types\Fields\Switcher_Field::class,
		'texteditor' => \Voxel\Post_Types\Fields\Texteditor_Field::class,
		'taxonomy' => \Voxel\Post_Types\Fields\Taxonomy_Field::class,
		'product' => \Voxel\Post_Types\Fields\Product_Field::class,
		'phone' => \Voxel\Post_Types\Fields\Phone_Field::class,
		'url' => \Voxel\Post_Types\Fields\Url_Field::class,
		'email' => \Voxel\Post_Types\Fields\Email_Field::class,
		'location' => \Voxel\Post_Types\Fields\Location_Field::class,
		'work-hours' => \Voxel\Post_Types\Fields\Work_Hours_Field::class,
		'image' => \Voxel\Post_Types\Fields\Image_Field::class,
		'file' => \Voxel\Post_Types\Fields\File_Field::class,
		'ui-step' => \Voxel\Post_Types\Fields\Ui_Step_Field::class,
		'ui-image' => \Voxel\Post_Types\Fields\Ui_Image_Field::class,
		'ui-heading' => \Voxel\Post_Types\Fields\Ui_Heading_Field::class,
		'ui-html' => \Voxel\Post_Types\Fields\Ui_Html_Field::class,
		'repeater' => \Voxel\Post_Types\Fields\Repeater_Field::class,
		'recurring-date' => \Voxel\Post_Types\Fields\Recurring_Date_Field::class,
		'post-relation' => \Voxel\Post_Types\Fields\Post_Relation_Field::class,
		'date' => \Voxel\Post_Types\Fields\Date_Field::class,
		'time' => \Voxel\Post_Types\Fields\Time_Field::class,
		'select' => \Voxel\Post_Types\Fields\Select_Field::class,
		'multiselect' => \Voxel\Post_Types\Fields\Multiselect_Field::class,
		'color' => \Voxel\Post_Types\Fields\Color_Field::class,
		'profile-avatar' => \Voxel\Post_Types\Fields\Profile\Profile_Avatar_Field::class,
		'profile-name' => \Voxel\Post_Types\Fields\Profile\Profile_Name_Field::class,
	] ),

	'filter_types' => apply_filters( 'voxel/filter-types', [
		'keywords' => \Voxel\Post_Types\Filters\Keywords_Filter::class,
		'date' => \Voxel\Post_Types\Filters\Date_Filter::class,
		'location' => \Voxel\Post_Types\Filters\Location_Filter::class,
		'open-now' => \Voxel\Post_Types\Filters\Open_Now_Filter::class,
		'order-by' => \Voxel\Post_Types\Filters\Order_By_Filter::class,
		'range' => \Voxel\Post_Types\Filters\Range_Filter::class,
		'stepper' => \Voxel\Post_Types\Filters\Stepper_Filter::class,
		'terms' => \Voxel\Post_Types\Filters\Terms_Filter::class,
		'availability' => \Voxel\Post_Types\Filters\Availability_Filter::class,
		'recurring-date' => \Voxel\Post_Types\Filters\Recurring_Date_Filter::class,
		'switcher' => \Voxel\Post_Types\Filters\Switcher_Filter::class,
		'user' => \Voxel\Post_Types\Filters\User_Filter::class,
		'followed-by' => \Voxel\Post_Types\Filters\Followed_By_Filter::class,
		'following-user' => \Voxel\Post_Types\Filters\Following_User_Filter::class,
		'following-post' => \Voxel\Post_Types\Filters\Following_Post_Filter::class,
		'relations' => \Voxel\Post_Types\Filters\Relations_Filter::class,
		'post-status' => \Voxel\Post_Types\Filters\Post_Status_Filter::class,
		'ui-heading' => \Voxel\Post_Types\Filters\Ui_Heading_Filter::class,
	] ),

	'orderby_types' => apply_filters( 'voxel/orderby-types', [
		'priority' => \Voxel\Post_Types\Order_By\Priority_Order::class,
		'relevance' => \Voxel\Post_Types\Order_By\Relevance_Order::class,
		'nearby' => \Voxel\Post_Types\Order_By\Nearby_Order::class,
		'rating' => \Voxel\Post_Types\Order_By\Rating_Order::class,
		'number-field' => \Voxel\Post_Types\Order_By\Number_Field_Order::class,
		'date-field' => \Voxel\Post_Types\Order_By\Date_Field_Order::class,
		'time-field' => \Voxel\Post_Types\Order_By\Time_Field_Order::class,
		'text-field' => \Voxel\Post_Types\Order_By\Text_Field_Order::class,
		'random' => \Voxel\Post_Types\Order_By\Random_Order::class,
		'date-created' => \Voxel\Post_Types\Order_By\Date_Created_Order::class,
		'date-modified' => \Voxel\Post_Types\Order_By\Date_Modified_Order::class,
		'latest-activity' => \Voxel\Post_Types\Order_By\Latest_Activity_Order::class,
		'recurring-date' => \Voxel\Post_Types\Order_By\Recurring_Date_Order::class,
	] ),

	'condition_types' => apply_filters( 'voxel/condition-types', [
		'date:empty' => \Voxel\Post_Types\Field_Conditions\Date_Empty::class,
		'date:gt' => \Voxel\Post_Types\Field_Conditions\Date_Gt::class,
		'date:lt' => \Voxel\Post_Types\Field_Conditions\Date_Lt::class,
		'date:not_empty' => \Voxel\Post_Types\Field_Conditions\Date_Not_Empty::class,

		'file:empty' => \Voxel\Post_Types\Field_Conditions\File_Empty::class,
		'file:not_empty' => \Voxel\Post_Types\Field_Conditions\File_Not_Empty::class,

		'number:empty' => \Voxel\Post_Types\Field_Conditions\Number_Empty::class,
		'number:equals' => \Voxel\Post_Types\Field_Conditions\Number_Equals::class,
		'number:gt' => \Voxel\Post_Types\Field_Conditions\Number_Gt::class,
		'number:gte' => \Voxel\Post_Types\Field_Conditions\Number_Gte::class,
		'number:lt' => \Voxel\Post_Types\Field_Conditions\Number_Lt::class,
		'number:lte' => \Voxel\Post_Types\Field_Conditions\Number_Lte::class,
		'number:not_empty' => \Voxel\Post_Types\Field_Conditions\Number_Not_Empty::class,
		'number:not_equals' => \Voxel\Post_Types\Field_Conditions\Number_Not_Equals::class,

		'switcher:checked' => \Voxel\Post_Types\Field_Conditions\Switcher_Checked::class,
		'switcher:unchecked' => \Voxel\Post_Types\Field_Conditions\Switcher_Unchecked::class,

		'taxonomy:contains' => \Voxel\Post_Types\Field_Conditions\Taxonomy_Contains::class,
		'taxonomy:not_contains' => \Voxel\Post_Types\Field_Conditions\Taxonomy_Not_Contains::class,
		'taxonomy:empty' => \Voxel\Post_Types\Field_Conditions\Taxonomy_Empty::class,
		'taxonomy:not_empty' => \Voxel\Post_Types\Field_Conditions\Taxonomy_Not_Empty::class,

		'text:empty' => \Voxel\Post_Types\Field_Conditions\Text_Empty::class,
		'text:not_empty' => \Voxel\Post_Types\Field_Conditions\Text_Not_Empty::class,
		'text:equals' => \Voxel\Post_Types\Field_Conditions\Text_Equals::class,
		'text:not_equals' => \Voxel\Post_Types\Field_Conditions\Text_Not_Equals::class,
		'text:contains' => \Voxel\Post_Types\Field_Conditions\Text_Contains::class,
	] ),

	'filter_condition_types' => apply_filters( 'voxel/filter-condition-types', [
		'common:is_empty' => \Voxel\Post_Types\Filter_Conditions\Is_Empty::class,
		'common:is_not_empty' => \Voxel\Post_Types\Filter_Conditions\Is_Not_Empty::class,

		'text:equals' => \Voxel\Post_Types\Filter_Conditions\Text_Equals::class,
		'text:not_equals' => \Voxel\Post_Types\Filter_Conditions\Text_Not_Equals::class,
		'text:contains' => \Voxel\Post_Types\Filter_Conditions\Text_Contains::class,

		'number:equals' => \Voxel\Post_Types\Filter_Conditions\Number_Equals::class,
		'number:gt' => \Voxel\Post_Types\Filter_Conditions\Number_Gt::class,
		'number:gte' => \Voxel\Post_Types\Filter_Conditions\Number_Gte::class,
		'number:lt' => \Voxel\Post_Types\Filter_Conditions\Number_Lt::class,
		'number:lte' => \Voxel\Post_Types\Filter_Conditions\Number_Lte::class,
		'number:not_equals' => \Voxel\Post_Types\Filter_Conditions\Number_Not_Equals::class,

		'taxonomy:contains' => \Voxel\Post_Types\Filter_Conditions\Taxonomy_Contains::class,
		'taxonomy:not_contains' => \Voxel\Post_Types\Filter_Conditions\Taxonomy_Not_Contains::class,
	] ),
];
