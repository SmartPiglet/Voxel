<script type="text/html" id="voxel-form-group-template">
	<component :is="tag" :class="{'ts-form-group': defaultClass}">
		<slot name="trigger"></slot>
		<teleport to="body">
			<transition name="form-popup">
				<form-popup
					ref="popup"
					v-if="$root.activePopup === popupKey"
					:class="wrapperClass"
					:controller-class="controllerClass"
					:target="popupTarget"
					:show-save="showSave"
					:show-clear="showClear"
					:show-clear-mobile="showClearMobile"
					:show-close="showClose"
					:save-label="saveLabel"
					:clear-label="clearLabel"
					:prevent-blur="preventBlur"
					@blur="onPopupBlur"
					@save="$emit('save', this);"
					@clear="$emit('clear', this);"
				>
					<slot name="popup"></slot>
					<template #controller>
						<slot name="controller"></slot>
					</template>
					<template #custom-actions>
						<slot name="custom-actions"></slot>
					</template>
				</form-popup>
			</transition>
		</teleport>
	</component>
</script>
