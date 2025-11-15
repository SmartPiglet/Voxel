<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="<?= esc_attr( $_REQUEST['page'] ) ?>" />
		<input type="hidden" name="plan" value="<?= esc_attr( $_REQUEST['plan'] ?? '' ) ?>" />
		<?php $table->views() ?>
		<?php $table->display() ?>
	</form>
</div>

<style type="text/css">
	.column-id {
		width: 60px;
	}

	.column-title img {
		margin-right: 10px;
		border-radius: 50px;
		display: inline-block;
		vertical-align: top;
		width: 32px;
		height: 32px;
	}

	.item-title {
		vertical-align: middle;
		display: inline-block;
	}

	.order-status {
		background: #e7e9ef;
		padding: 2px 7px;
		display: inline-block;
		border-radius: 4px;
		font-weight: 500;
		color: #626f91;
	}

	.vx-orange {
		background: rgba(255, 114, 36, .1);
		color: rgba(255, 114, 36, 1);
	}

	.vx-green {
		background: rgba(0, 197, 109, .1);
		color: rgba(0, 197, 109, 1);
	}

	.vx-neutral {
		background: rgba(83, 91, 110, .1);
		color: rgba(83, 91, 110, 1);
	}

	.vx-red {
		background: rgba(244, 59, 59, .1);
		color: rgba(244, 59, 59, 1);
	}

	.vx-blue {
		background: rgba(83, 70, 229, .1);
		color: rgba(83, 70, 229, 1);
	}

	.ts-search-input {
		width: 250px;
		vertical-align: top;
	}

	#the-list td {
		vertical-align: middle;
	}
</style>
