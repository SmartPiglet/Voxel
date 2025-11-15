<?php

namespace Voxel\Controllers\Frontend\Timeline;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Timeline_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return \Voxel\get( 'settings.timeline.enabled', true );
	}

	protected function dependencies() {
		new Status_Controller;
		new Status_Feed_Controller;
		new Comment_Controller;
		new Comment_Feed_Controller;
		new Suggestions_Controller;
	}

	protected function hooks() {
		//
	}

}
