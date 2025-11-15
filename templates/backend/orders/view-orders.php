<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<form class="vx-orders" method="get">
		<input type="hidden" name="status" value="<?= esc_attr( $_REQUEST['status'] ?? '' ) ?>" />
		<input type="hidden" name="page" value="<?= esc_attr( $_REQUEST['page'] ) ?>" />
		<?php $table->views() ?>
		<?php $table->display() ?>
	</form>
</div>

<style type="text/css">
	.item-details {
		display: flex;
    	align-items: center;
    	gap: 10px;
	}

	.item-details .item-image img {
		border-radius: 50px;
		vertical-align: middle;
		width: 32px;
		height: 32px;
	}

	#the-list td {
		vertical-align: middle;
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

	.tablenav.top {
		input[type="text"], select {
			max-width: 150px;
			vertical-align: top;
		}
	}
</style>

<script type="text/javascript">
	jQuery( $ => {
		if ( window.matchMedia('screen and (max-width: 782px)').matches ) {
			$('.vx-orders tr').each( function() {
				$(this).children(':eq(1)').after($(this).children(':eq(0)'));
			} );
		}
	} );
</script>
