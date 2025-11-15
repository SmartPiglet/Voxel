<?php

namespace Voxel\Dynamic_Data;

use \Voxel\Dynamic_Data\Data_Groups as Data_Groups;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Group {

	public static function Site() {
		return Data_Groups\Site\Site_Data_Group::get();
	}

	public static function Term( ?\Voxel\Term $term ) {
		if ( $term === null ) {
			return null;
		}

		return Data_Groups\Term\Term_Data_Group::get( $term );
	}

	public static function Message( ?\Voxel\Modules\Direct_Messages\Message $message ) {
		if ( $message === null ) {
			return null;
		}

		return new Data_Groups\Messages\Message_Data_Group( $message );
	}

	// Users
	public static function User( ?\Voxel\User $user ) {
		if ( $user === null ) {
			return null;
		}

		return Data_Groups\User\User_Data_Group::get( $user );
	}

	public static function User_Membership( ?\Voxel\Modules\Paid_Memberships\Membership\Base_Membership $membership ) {
		if ( $membership === null ) {
			return null;
		}

		return new Data_Groups\User\Membership_Data_Group( $membership );
	}

	// Posts
	public static function Post( ?\Voxel\Post $post ) {
		if ( $post === null ) {
			return null;
		}

		return Data_Groups\Post\Post_Data_Group::get( $post );
	}

	public static function Simple_Post( ?\Voxel\Post $post ) {
		if ( $post === null ) {
			return null;
		}

		return Data_Groups\Post\Simple_Post_Data_Group::get( $post );
	}

	public static function Post_Relation_Request( array $relation_ids ) {
		return new Data_Groups\Post\Relations\Relation_Request_Data_Group( $relation_ids );
	}

	// Timeline
	public static function Timeline_Status( ?\Voxel\Timeline\Status $status ) {
		if ( $status === null ) {
			return null;
		}

		return Data_Groups\Timeline\Status_Data_Group::get( $status );
	}

	public static function Timeline_Review( ?\Voxel\Timeline\Status $review ) {
		if ( $review === null ) {
			return null;
		}

		return Data_Groups\Timeline\Review_Data_Group::get( $review );
	}

	public static function Timeline_Reply( ?\Voxel\Timeline\Reply $reply ) {
		if ( $reply === null ) {
			return null;
		}

		return Data_Groups\Timeline\Reply_Data_Group::get( $reply );
	}

	// Orders
	public static function Order( ?\Voxel\Product_Types\Orders\Order $order ) {
		if ( $order === null ) {
			return null;
		}

		return Data_Groups\Orders\Order_Data_Group::get( $order );
	}

	public static function Order_Item_Booking( ?\Voxel\Product_Types\Order_Items\Order_Item $order_item ) {
		if ( $order_item === null ) {
			return null;
		}

		return new Data_Groups\Orders\Booking_Data_Group( $order_item );
	}

	public static function Order_Item_Promotion( ?\Voxel\Product_Types\Order_Items\Order_Item $order_item ) {
		if ( $order_item === null ) {
			return null;
		}

		return new Data_Groups\Orders\Promotion_Data_Group( $order_item );
	}

	public static function Noop() {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new \Voxel\Dynamic_Data\Data_Groups\Noop_Data_Group;
		}

		return $instance;
	}
}
