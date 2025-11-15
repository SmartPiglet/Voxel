<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Select2_Control extends \Elementor\Control_Select2 {

	/**
	 * Override Select2 control's content template to output selected
	 * items in the order they were chosen, providing drag&drop support.
	 *
	 * @since 1.0
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php echo $control_uid; ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
					<# if ( data.multiple ) { #>
						<#
							var value = data.controlValue;
							if ( typeof value === 'string' ) {
								value = [ value ];
							}
						#>
						<# if ( Array.isArray( value ) ) {
							_.each( value, function( selected_value ) {
								var label = data.options[ selected_value ];
								if ( label ) { #>
									<option selected value="{{ selected_value }}">{{{ label }}}</option>
								<# }
							} );
						} #>

						<# _.each( data.options, function( option_title, option_value ) {
							if ( Array.isArray( value ) && value.indexOf( option_value ) !== -1 ) {
								return;
							}
						#>
							<option value="{{ option_value }}">{{{ option_title }}}</option>
						<# } ); #>
					<# } else { #>
						<# _.each( data.options, function( option_title, option_value ) {
							var value = data.controlValue;
							if ( typeof value == 'string' ) {
								var selected = ( option_value === value ) ? 'selected' : '';
							} else if ( null !== value ) {
								var value = _.values( value );
								var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : '';
							}
						#>
							<option {{ selected }} value="{{ option_value }}">{{{ option_title }}}</option>
						<# } ); #>
					<# } #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

}
