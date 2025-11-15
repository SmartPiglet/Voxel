<?php
/**
 * Term custom fields.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<tr class="form-field">
	<th>Custom fields</th>
	<td>
		<div id="voxel-term-settings" class="ts-theme-options ts-container" data-config="<?= esc_attr( wp_json_encode( [
			'fields' => $fields,
		] ) ) ?>">
			<div class="x-row">
				<div class="ts-form-group x-col-12">
					<label>Icon</label>
					<icon-picker v-model="fields.icon"></icon-picker>
				</div>

				<div class="ts-form-group x-col-12">
					<label>Image</label>
					<media-select
						v-model="fields.image"
						:file-type="['image/jpeg','image/png','image/webp','image/avif']"
						:multiple="false"
					></media-select>
				</div>

				<div class="ts-form-group x-col-12">
					<label>Area</label>
					<input type="text" ref="addressInput" :value="fields.area.address">
					<div v-if="fields.area.swlat">
						<p>
							SW {{ fields.area.swlat }},{{ fields.area.swlng }};
							NE: {{ fields.area.nelat }},{{ fields.area.nelng }}
						</p>
					</div>
				</div>

				<?= \Voxel\Utils\Form_Models\Color_Model::render( [
					'v-model' => 'fields.color',
					'classes' => 'x-col-12',
					'label' => 'Accent color',
				] ) ?>

				

				<div class="ts-form-group x-col-12 hide">
					<!-- <pre>{{ $data }}</pre> -->
					<input type="text" name="voxel_icon" :value="fields.icon">
					<input type="text" name="voxel_image" :value="fields.image">
					<input type="text" name="voxel_color" :value="fields.color">
					<input type="text" name="voxel_area" :value="JSON.stringify(fields.area)">
				</div>
			</div>
		</div>
	</td>
</tr>
