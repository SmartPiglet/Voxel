export default {
	props: {
		provider: Object,
		settings: Object,
		data: Object,
	},

	data() {
		return {
			//
		};
	},

	methods: {
		setupWebhook( e, mode ) {
			const btn = e.target;
			btn?.classList.add('vx-disabled');
			jQuery.post( Voxel_Config.ajax_url, {
				action: 'paddle.admin.setup_webhook',
				mode: mode,
				api_key: this.settings[ mode ].api_key,
			} ).always( response => {
				btn?.classList.remove('vx-disabled');
				if ( response.success ) {
					if ( response.id ) {
						this.settings[ mode ].webhook.id = response.id;
					}

					if ( response.secret ) {
						this.settings[ mode ].webhook.secret = response.secret;
					}

					Voxel_Backend.alert( response.message );
				} else {
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		},
	},
};
