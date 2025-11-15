export default {
	template: `<!--minify-->
		<div class="order-event">
			<div class="order-event-icon vx-green">
				<icon-checkmark/>
			</div>
			<b>{{ data.l10n.block_label }}</b>
		</div>
		<div class="order-event">
			

			<div v-for="limit in data.limits" class="order-event-box">
				<ul class="ts-cart-list simplify-ul">
					<li>
						<div class="cart-image">
							<img width="150" height="150" :src="data.image" class="ts-status-avatar" decoding="async">
						</div>
						<div class="cart-item-details">
							<div class="order-item-title">
								<a>{{ limit.label }}</a>
								<span>{{ limit.usage.text }}</span>
							</div>
							<span>{{ limit.description }}</span>
						</div>
					</li>
				</ul>
			
			</div>
		</div>
	`,
	props: {
		orderItem: Object,
		parent: Object,
		order: Object,
		data: Object,
	},

	methods: {
		loadMorePosts( limit ) {
			jQuery.get( `${Voxel_Config.ajax_url}&action=paid_listings.order.load_more_recents`, {
				order_id: this.order.id,
				order_item_id: this.orderItem.id,
				index: this.data.limits.indexOf( limit ),
				cursor: limit.recents.list.length,
				_wpnonce: this.data._wpnonce,
			} ).always( response => {
				if ( response.success ) {
					limit.recents.list.push( ...response.posts.list );
					limit.recents.has_more = response.posts.has_more;
				} else {
					Voxel.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		},
	},
};
