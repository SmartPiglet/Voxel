<?php

namespace Voxel\Utils\Async_Requests;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Async_Email extends WP_Async_Request {

	protected $action = 'async_email';

	protected function handle() {
		$emails = wp_unslash( $_POST['emails'] ?? [] );
		foreach ( $emails as $email ) {
			wp_mail(
				$email['recipient'],
				$email['subject'],
				\Voxel\email_template( $email['message'] ),
				$email['headers']
			);
		}
	}
}
