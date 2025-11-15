<?php

namespace Voxel\Dynamic_Data;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Config {

	public static function get_data_groups(): array {
		static $list = null;

		if ( $list === null ) {
			$list = apply_filters( 'voxel/dynamic-data/groups', [
				'message' => \Voxel\Dynamic_Data\Data_Groups\Messages\Message_Data_Group::class,
				'orders/booking' => \Voxel\Dynamic_Data\Data_Groups\Orders\Booking_Data_Group::class,
				'order' => \Voxel\Dynamic_Data\Data_Groups\Orders\Order_Data_Group::class,
				'orders/promotion' => \Voxel\Dynamic_Data\Data_Groups\Orders\Promotion_Data_Group::class,
				'posts/relation-request' => \Voxel\Dynamic_Data\Data_Groups\Post\Relations\Relation_Request_Data_Group::class,
				'post' => \Voxel\Dynamic_Data\Data_Groups\Post\Post_Data_Group::class,
				'simple-post' => \Voxel\Dynamic_Data\Data_Groups\Post\Simple_Post_Data_Group::class,
				'site' => \Voxel\Dynamic_Data\Data_Groups\Site\Site_Data_Group::class,
				'term' => \Voxel\Dynamic_Data\Data_Groups\Term\Term_Data_Group::class,
				'timeline/reply' => \Voxel\Dynamic_Data\Data_Groups\Timeline\Reply_Data_Group::class,
				'timeline/review' => \Voxel\Dynamic_Data\Data_Groups\Timeline\Review_Data_Group::class,
				'timeline/status' => \Voxel\Dynamic_Data\Data_Groups\Timeline\Status_Data_Group::class,
				'user/membership' => \Voxel\Dynamic_Data\Data_Groups\User\Membership_Data_Group::class,
				'user' => \Voxel\Dynamic_Data\Data_Groups\User\User_Data_Group::class,
				'noop' => \Voxel\Dynamic_Data\Data_Groups\Noop_Data_Group::class,
				'value' => \Voxel\Dynamic_Data\Data_Groups\Value_Data_Group::class,
			] );
		}

		return $list;
	}

	public static function get_modifiers(): array {
		static $list = null;

		if ( $list === null ) {
			$list = apply_filters( 'voxel/dynamic-data/modifiers', [
				'append' => \Voxel\Dynamic_Data\Modifiers\Append_Modifier::class,
				'capitalize' => \Voxel\Dynamic_Data\Modifiers\Capitalize_Modifier::class,
				'currency_format' => \Voxel\Dynamic_Data\Modifiers\Currency_Format_Modifier::class,
				'date_format' => \Voxel\Dynamic_Data\Modifiers\Date_Format_Modifier::class,
				'fallback' => \Voxel\Dynamic_Data\Modifiers\Fallback_Modifier::class,
				'list' => \Voxel\Dynamic_Data\Modifiers\List_Modifier::class,
				'count' => \Voxel\Dynamic_Data\Modifiers\Count_Modifier::class,
				'first' => \Voxel\Dynamic_Data\Modifiers\First_Modifier::class,
				'last' => \Voxel\Dynamic_Data\Modifiers\Last_Modifier::class,
				'nth' => \Voxel\Dynamic_Data\Modifiers\Nth_Modifier::class,
				'number_format' => \Voxel\Dynamic_Data\Modifiers\Number_Format_Modifier::class,
				'round' => \Voxel\Dynamic_Data\Modifiers\Round_Modifier::class,
				'abbreviate' => \Voxel\Dynamic_Data\Modifiers\Abbreviate_Modifier::class,
				'prepend' => \Voxel\Dynamic_Data\Modifiers\Prepend_Modifier::class,
				'time_diff' => \Voxel\Dynamic_Data\Modifiers\Time_Diff_Modifier::class,
				'to_age' => \Voxel\Dynamic_Data\Modifiers\To_Age_Modifier::class,
				'truncate' => \Voxel\Dynamic_Data\Modifiers\Truncate_Modifier::class,
				'replace' => \Voxel\Dynamic_Data\Modifiers\Replace_Modifier::class,

				'is_empty' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Empty_Control::class,
				'is_not_empty' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Not_Empty_Control::class,
				'is_equal_to' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Equal_To_Control::class,
				'is_not_equal_to' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Not_Equal_To_Control::class,
				'is_greater_than' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Greater_Than_Control::class,
				'is_less_than' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Less_Than_Control::class,
				'is_between' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Between_Control::class,
				'is_checked' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Checked_Control::class,
				'is_unchecked' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Is_Unchecked_Control::class,
				'contains' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Contains_Control::class,
				'does_not_contain' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Does_Not_Contain_Control::class,
				'then' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Then_Control::class,
				'else' => \Voxel\Dynamic_Data\Modifiers\Control_Structures\Else_Control::class,
			] );
		}

		return $list;
	}

	public static function get_visibility_rules(): array {
		static $list = null;

		if ( $list === null ) {
			$list = apply_filters( 'voxel/dynamic-data/visibility-rules', [
				'dtag' => \Voxel\Dynamic_Data\Visibility_Rules\DTag_Rule::class,

				'user:logged_in' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Logged_In::class,
				'user:logged_out' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Logged_Out::class,
				'user:plan' => \Voxel\Dynamic_Data\Visibility_Rules\User_Plan_Is::class,
				'user:role' => \Voxel\Dynamic_Data\Visibility_Rules\User_Role_Is::class,
				'user:is_author' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Author::class,
				'user:can_create_post' => \Voxel\Dynamic_Data\Visibility_Rules\User_Can_Create_Post::class,
				'user:can_edit_post' => \Voxel\Dynamic_Data\Visibility_Rules\User_Can_Edit_Post::class,
				'user:is_verified' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Verified::class,
				'user:is_vendor' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Vendor::class,
				'user:has_bought_product' => \Voxel\Dynamic_Data\Visibility_Rules\User_Has_Bought_Product::class,
				'user:is_customer_of_author' => \Voxel\Dynamic_Data\Visibility_Rules\User_Is_Customer_Of_Author::class,
				'user:follows_post' => \Voxel\Dynamic_Data\Visibility_Rules\User_Follows_Post::class,
				'user:follows_author' => \Voxel\Dynamic_Data\Visibility_Rules\User_Follows_Author::class,

				'author:plan' => \Voxel\Dynamic_Data\Visibility_Rules\Author_Plan_Is::class,
				'author:role' => \Voxel\Dynamic_Data\Visibility_Rules\Author_Role_Is::class,
				'author:is_verified' => \Voxel\Dynamic_Data\Visibility_Rules\Author_Is_Verified::class,
				'author:is_vendor' => \Voxel\Dynamic_Data\Visibility_Rules\Author_Is_Vendor::class,

				'template:is_page' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Page::class,
				'template:is_child_of_page' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Child_Of_Page::class,
				'template:is_single_post' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Single_Post::class,
				'template:is_post_type_archive' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Post_Type_Archive::class,
				'template:is_author' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Author::class,
				'template:is_single_term' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Single_Term::class,
				'template:is_homepage' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_Homepage::class,
				'template:is_404' => \Voxel\Dynamic_Data\Visibility_Rules\Template_Is_404::class,

				'post:is_verified' => \Voxel\Dynamic_Data\Visibility_Rules\Post_Is_Verified::class,

				'product:is_available' => \Voxel\Dynamic_Data\Visibility_Rules\Product_Is_Available::class,
				'product_type:is' => \Voxel\Dynamic_Data\Visibility_Rules\Product_Type_Is::class,
			] );
		}

		return $list;
	}

}
