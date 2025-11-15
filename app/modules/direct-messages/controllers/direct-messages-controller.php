<?php

namespace Voxel\Modules\Direct_Messages\Controllers;

use \Voxel\Modules\Direct_Messages as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Direct_Messages_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! \Voxel\get( 'settings.addons.direct_messages.enabled', true );
	}

	protected function dependencies() {
		new Frontend\Inbox_Controller;
	}

	protected function hooks() {
		$this->on( 'elementor/widgets/register', '@register_widgets', 1100 );
		$this->filter( 'voxel/advanced-list/actions', '@register_direct_message_actions' );
		$this->on( 'voxel/advanced-list/action:direct_message', '@render_direct_message_action', 10, 2 );
		$this->on( 'voxel/advanced-list/action:direct_message_user', '@render_direct_message_user_action', 10, 2 );
	}

	protected function register_direct_message_actions( $actions ) {
		$actions['direct_message'] = __( 'Message post', 'voxel-elementor' );
		$actions['direct_message_user'] = __( 'Message post author', 'voxel-elementor' );

		return $actions;
	}

	protected function render_direct_message_action( $widget, $action ) {
		require locate_template( 'app/modules/direct-messages/templates/frontend/direct-message-action.php' );
	}

	protected function render_direct_message_user_action( $widget, $action ) {
		require locate_template( 'app/modules/direct-messages/templates/frontend/direct-message-user-action.php' );
	}

	protected function register_widgets() {
		$manager = \Elementor\Plugin::instance()->widgets_manager;
		$manager->register( new Module\Widgets\Messages_Widget );
	}
}
