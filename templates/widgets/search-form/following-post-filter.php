<script type="text/html" id="search-form-following-post-filter">
	<form-group :popup-key="filter.id" ref="formGroup" v-if="filter.value !== null" :wrapper-class="repeaterId">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">
					<template v-if="filter.props.post_id">
						#{{ filter.props.post_id }}
					</template>
				</div>
			</div>
		</template>
	</form-group>
</script>
