<?php

namespace Voxel\Utils\Async_Requests;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Email_Queue extends WP_Background_Process {

	protected $action = 'email_queue';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		wp_mail(
			$item['recipient'],
			$item['subject'],
			\Voxel\email_template( $item['message'] ),
			$item['headers']
		);

		return false;
	}
}
