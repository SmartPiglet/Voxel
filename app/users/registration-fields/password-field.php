<?php

namespace Voxel\Users\Registration_Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Password_Field extends Base_Registration_Field  {
	protected $props = [
		'key' => 'voxel:auth-password',
		'label' => 'Password',
	];
}
