<?php

/*
 * Settings Template
 */

$settings = GPTE()->settings->get_settings();
$settings_nonce_data = GPTE()->settings->get_nonce();

if( did_action( 'gpte/admin/settings/settings_saved' ) ){
	echo GPTE()->helpers->create_admin_notice( 'The settings have been successfully updated. Please refresh the page.', 'success', true );
}

?>
<div class="gpte-container">

    <form id="gpte-main-settings-form" method="post" action="">

		<div class="gpte-title-area mb-4">
			<h2><?php echo __( 'Global Settings', 'gpte' ); ?></h2>
			<p><?php echo sprintf( __( 'Below you can customize the global settings for %s. Please make sure to read the settings descriptions carefully before saving the settings.', 'gpte' ), GPTE_NAME ); ?></p>
		</div>

		<div class="gpte-settings">
			<?php foreach( $settings as $setting_name => $setting ) :

			if( isset( $setting['dangerzone'] ) && $setting['dangerzone'] ){
				continue;
			}

			$is_checked = ( $setting['type'] == 'checkbox' && $setting['value'] == 'yes' ) ? 'checked' : '';
			$value = ( $setting['type'] != 'checkbox' ) ? $setting['value'] : '1';
			$value = ( empty( $value ) && ! empty( $setting['default_value'] ) ) ? $setting['default_value'] : $value;

			$validated_atributes = '';
			if( isset( $setting['attributes'] ) ){
				foreach( $setting['attributes'] as $attribute_name => $attribute_value ){
					$validated_atributes .=  $attribute_name . '="' . $attribute_value . '" ';
				}
			}

			?>
			<div class="gpte-setting">
				<div class="gpte-setting__title">
				<label for="<?php echo $setting['id']; ?>"><?php echo $setting['label']; ?></label>
				</div>
				<div class="gpte-setting__desc">
				<?php echo wpautop( $setting['description'] ); ?>
				</div>
				<div class="gpte-setting__action d-flex justify-content-end gpte-text-left " style="width:200px;max-width:200px;">
				<?php if( in_array( $setting['type'], array( 'checkbox' ) ) ) : ?>
					<div class="gpte-toggle gpte-toggle--on-off">
					<input type="<?php echo $setting['type']; ?>" id="<?php echo $setting['id']; ?>" name="<?php echo $setting_name; ?>" class="gpte-toggle__input" <?php echo $is_checked; ?>>
					<label class="gpte-toggle__btn" for="<?php echo $setting['id']; ?>"></label>
					</div>
				<?php elseif( in_array( $setting['type'], array( 'select' ) ) ) : ?>
					<select
						class="gpte-form-input"
						name="<?php echo $setting_name; ?><?php echo ( isset( $setting['multiple'] ) && $setting['multiple'] ) ? '[]' : ''; ?>" <?php echo $validated_atributes; ?> <?php echo ( isset( $setting['multiple'] ) && $setting['multiple'] ) ? 'multiple' : ''; ?>
					>
						<?php if( isset( $setting['choices'] ) ) : ?>
							<?php foreach( $setting['choices'] as $choice_name => $choice_label ) :

								//Compatibility with 4.3.0
								if( is_array( $choice_label ) ){
									if( isset( $choice_label['label'] ) ){
										$choice_label = $choice_label['label'];
									} else {
										$choice_label = $choice_name;
									}
								}

								$selected = '';
								if( is_array( $setting['value'] ) ){
									if( isset( $setting['value'][ $choice_name ] ) ){
										$selected = 'selected="selected"';
									}
								} else {
									if( (string) $setting['value'] === (string) $choice_name ){
										$selected = 'selected="selected"';
									}
								}
							?>
							<option value="<?php echo $choice_name; ?>" <?php echo $selected; ?>><?php echo __( $choice_label, 'gpte' ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				<?php else : ?>
					<input id="<?php echo $setting['id']; ?>" name="<?php echo $setting_name; ?>" type="<?php echo $setting['type']; ?>" class="regular-text" value="<?php echo $value; ?>" />
				<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="gpte-text-center mt-4 pt-3">
			<button class="gpte-btn gpte-btn--secondary active" type="submit" name="gpte_settings_submit">
			<span><?php echo __( 'Save All Settings', 'gpte' ); ?></span>
			</button>
		</div>

		<?php echo GPTE()->helpers->get_nonce_field( $settings_nonce_data ); ?>
    </form>
</div>