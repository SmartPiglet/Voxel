<script type="text/html" id="search-form-switcher-filter">
	<div v-if="filter.props.openInPopup" class="ts-form-group">
		<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
		<div class="ts-filter" @click.prevent="toggle" :class="{'ts-filled': filter.value !== null}">
			<span v-html="filter.icon"></span>
			<div class="ts-filter-text">
				<span>{{ filter.props.placeholder }}</span>
			</div>
		</div>
	</div>
	<div v-else class="ts-form-group switcher-label ts-inline-filter">
		<label class="ts-keep-visible">
			<div class="switch-slider">
				<div class="onoffswitch">
					<input type="checkbox" class="onoffswitch-checkbox" :checked="filter.value !== null" @change="toggle" tabindex="0">
					<label class="onoffswitch-label" @click.prevent="toggle"></label>
				</div>
			</div>
			{{ filter.props.placeholder }}
		</label>
	</div>
</script>
