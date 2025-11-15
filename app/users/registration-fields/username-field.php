<?php

namespace Voxel\Users\Registration_Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Username_Field extends Base_Registration_Field {
	protected $props = [
		'key' => 'voxel:auth-username',
		'label' => 'Username',
	];
}
