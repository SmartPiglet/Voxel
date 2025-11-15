<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template('templates/widgets/timeline/status-composer/status-composer.php');
require_once locate_template('templates/widgets/timeline/status-feed/status-feed.php');
require_once locate_template('templates/widgets/timeline/status/status.php');
require_once locate_template('templates/widgets/timeline/comment-composer/comment-composer.php');
require_once locate_template('templates/widgets/timeline/comment-feed/comment-feed.php');
require_once locate_template('templates/widgets/timeline/comment/comment.php');
require_once locate_template('templates/widgets/timeline/partials/_dropdown-list.php');
?>

<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $cfg ) ) ?></script>
<script type="text/json" class="vxconfig__icons"><?= wp_json_encode( $icons ) ?></script>
<div class="vxfeed" v-cloak id="tl:<?= esc_attr( $this->get_id() ) ?>">
	<status-feed></status-feed>
</div>
