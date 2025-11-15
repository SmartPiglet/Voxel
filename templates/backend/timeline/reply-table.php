<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="<?= esc_attr( $_REQUEST['page'] ) ?>" />
		<?php $table->views() ?>
		<?php $table->search_box( 'Search', 'search' ) ?>
		<?php $table->display() ?>
	</form>
</div>
<style type="text/css">
	.column-title img {
		margin-right: 10px;
		border-radius: 50px;
		display: inline-block;
		vertical-align: top;
		width: 24px;
		height: 24px;
	}

	.item-title {
		vertical-align: middle;
		display: inline-block;
	}

	.column-title, #title { width: 30%; }
	.column-content, #content { width: 50%; }
	.column-date, #date { width: 20%; }

	input[name="search_id"], input[name="search_user_id"], input[name="search_post_id"], select[name="search_feed"] {
		width: 100px;
	}

	select[name="search_feed"] {
		margin-top: -3px;
		min-height: auto;
	}

	.ts__status-content {
		white-space: pre-wrap;
		word-wrap: break-word;
		word-break: break-all;
		padding: 5px 8px;
		max-height: 160px;
		tab-size: 2;
		overflow-x: hidden;
		overflow-y: auto;
		background: #fff;
		border-radius: 5px;
		border: 1px solid #ccc;
	}

	.ts__status-files a {
		display: block;
	}
</style>

<script type="text/javascript">
	Array.from( document.querySelectorAll('.tl__action') ).forEach( item => {
		item.addEventListener( 'click', e => {
			e.preventDefault();
			const action = e.target.dataset.action;
			const reply_id = e.target.closest('.row-actions').dataset.replyId;
			e.target.closest('tr').classList.add('vx-disabled');

			if ( ['decline', 'delete'].includes(action) ) {
				if ( ! confirm('This action cannot be undone. Do you want to proceed?') ) {
					e.target.closest('tr').classList.remove('vx-disabled');
					return;
				}
			}

			jQuery.post( `${Voxel_Config.ajax_url}&action=backend.timeline.reply.${action}`, {
				reply_id: reply_id,
				_wpnonce: <?= wp_json_encode( wp_create_nonce( 'vx_timeline_backend' ) ) ?>,
			} ).always( response => {
				if ( response.success ) {
					location.reload();
				} else {
					e.target.closest('tr').classList.remove('vx-disabled');
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		} );
	} );

	const indexBtn = document.getElementById('tl__reindex');
	indexBtn.addEventListener( 'click', e => {
		e.preventDefault();
		indexBtn.classList.add('vx-disabled');

		const processBatch = () => {
			jQuery.post( `${Voxel_Config.ajax_url}&action=backend.timeline.reply.index_all_replies`, {
				_wpnonce: <?= wp_json_encode( wp_create_nonce( 'vx_timeline_backend' ) ) ?>,
			} ).always( response => {
				if ( response.success ) {
					if ( response.has_more ) {
						indexBtn.innerText = `${response.offset} / ${response.total}`;
						processBatch();
					} else {
						indexBtn.innerText = 'Done';
					}
				} else {
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		};

		processBatch();
	} );
</script>