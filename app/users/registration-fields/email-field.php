<?php

namespace Voxel\Users\Registration_Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Email_Field extends Base_Registration_Field  {
	protected $props = [
		'key' => 'voxel:auth-email',
		'label' => 'Email address',
	];
}
