<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?= get_admin_page_title() ?></h1>
	<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-paid-listings&add_new_plan=1' ) ) ?>" class="page-title-action">Add new</a>
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

	.item-user {
		display: flex;
		justify-content: flex-start;
		gap: 10px;
	}

	.column-title img {
		border-radius: 50px;
		width: 32px;
		height: 32px;
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

	.ts-search-order {
		width: 100px;
		vertical-align: top;
	}

	#the-list td {
		vertical-align: middle;
	}

	/* package list */
	.price-amount {
		display: block;
		opacity: .9;
	}

	.column-usage {
		text-align: right !important;
	}
</style>
