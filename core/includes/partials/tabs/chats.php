<?php

$openai_api_key = get_option( 'gpte_openai_api_key' );
$chats_nonce = GPTE()->settings->get_nonce();
$clear_form_url = GPTE()->helpers->get_current_url( true, true );
$clean_url = GPTE()->helpers->get_current_url( false, true );

//Create chat
if( isset( $_POST['gpte-chats-name'] ) ){
	if ( check_admin_referer( $chats_nonce['action'], $chats_nonce['arg'] ) ) {

		if( GPTE()->helpers->current_user_can( GPTE()->settings->get_admin_cap( 'gpte-page-chats-add-chat' ), 'gpte-page-chats-add-chat' ) ){
			$chat_title = isset( $_POST['gpte-chats-name'] ) ? wp_strip_all_tags( sanitize_text_field( $_POST['gpte-chats-name'] ) ) : '';

			if( ! empty( $chat_title ) ){

				$check = GPTE()->chats->create_chat( array(
					'post_title' => $chat_title,
					'post_status' => 'publish',
				) );

				if( ! empty( $check ) && is_numeric( $check ) ){

					if( ! headers_sent() ){
						$new_chat_url = GPTE()->helpers->built_url( $clean_url, array_merge( $_GET, array( 'chat_id' => $check, ) ) );
						wp_redirect( $new_chat_url );
						die();
					}

				} else {
					echo GPTE()->helpers->create_admin_notice( 'An error occured while creating the chat. Please try again.', 'warning', true );
				}

			}
		}

	}
}

?>
<div class="gpte-container">
  <div class="gpte-title-area mb-4">
    <div class="gpte-title-area-wrapper d-flex mb-4">
      <h1 class="mb-0"><?php echo __( 'Chats', 'gpte' ); ?></h1>
      <a href="#" class="gpte-btn gpte-btn--sm gpte-btn--secondary ml-2 d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#addAuthTemplateModal"><?php echo __( 'Create Chat', 'gpte' ); ?></a>
    </div>
    
    <p>
      <?php echo sprintf(__( 'Chats are your way of automating within and outside of your WordPress website. If you would like to learn more about our chats, visit <a class="text-secondary" title="Visit GPTEverest website" href="%s" target="_blank">our website</a>.', 'gpte' ), 'https://gpteverest.com'); ?>
    </p>
  </div>

  <?php 
    
    // Creating an instance
    $table = GPTE()->chats->get_chat_lists_table_class();
    // Prepare table
    $table->prepare_items();
    // Display table
    $table->display();

  ?>

</div>

<div class="modal fade" id="addAuthTemplateModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"><?php echo __( 'Create Chat', 'gpte' ); ?></h3>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M13 1L1 13" stroke="#0E0A1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M1 1L13 13" stroke="#0E0A1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
				</button>
			</div>
			
			<?php if( empty( $openai_api_key ) ) : ?>
				<div class="modal-header">
					<p class="text-danger"><?php echo __( 'Please add your OpenAI API key within the settings first.', 'gpte' ); ?></p>
				</div>
			<?php else : ?>
				<form action="<?php echo $clear_form_url; ?>" method="post">
					<div class="modal-body">
					<label class="gpte-form-label" for="gpte-chats-name"><?php echo __( 'Chat Name', 'gpte' ); ?></label>
								<input class="gpte-form-input w-100" type="text" id="gpte-chats-name" name="gpte-chats-name" placeholder="<?php echo __( 'Chat Name', 'gpte' ); ?>" />
					</div>
					<div class="modal-footer">
								<?php wp_nonce_field( $chats_nonce['action'], $chats_nonce['arg'] ); ?>
								<input type="submit" name="submit" id="submit" class="gpte-btn gpte-btn--secondary w-100" value="<?php echo __( 'Create', 'gpte' ); ?>">
					</div>
				</form>
			<?php endif; ?>
		
		</div>
	</div>
</div>

<script>
  jQuery(document).ready(function($) {

    /**
     * Chat:
     *
     * Delete chat template
     */
    $(document).on( "click", ".gpte-delete-chat-template", function(e) {
		e.preventDefault();

		var $this = $(this);
		var dataTemplateId = $this.data( 'gpte-chat-id' );
		var wrapperHtml = '';

		if ( dataTemplateId && confirm( "Are you sure you want to delete this chat?" ) ) {

			// Prevent from clicking again
			if ( $this.hasClass( 'is-loading' ) ) {
			return;
			}

			$this.addClass( 'is-loading' );
			$this.find('img').animate( { 'opacity': 0 }, 150 );

			$.ajax({
			url: gpte.ajax_url,
			type: 'post',
			data: {
				action: 'gpte_chats_handler',
				gpte_nonce: gpte.ajax_nonce,
				handler: 'delete_chat',
				language: gpte.language,
				chat_id: dataTemplateId,
			},
			success: function( res ) {

				console.log(res);

				$this.removeClass( 'is-loading' );
				$this.find('img').animate( { 'opacity': 1 }, 150 );

				if ( res[ 'success' ] === 'true' || res[ 'success' ] === true ) {
				$this.closest('tr').remove();
				}
			},
			error: function( errorThrown ) {
				$this.removeClass( 'is-loading' );
				console.log( errorThrown );
			}
			});
		}

    });

    /**
     * Chat:
     *
     * Duplicate chat template
     */
    $(document).on( "click", ".gpte-duplicate-chat-template", function(e) {
		e.preventDefault();

		var $this = $(this);
		var dataTemplateId = $this.data( 'gpte-template-id' );
		var wrapperHtml = '';

		if ( dataTemplateId && confirm( "Are you sure you want to duplicate this template?" ) ) {

		// Prevent from clicking again
		if ( $this.hasClass( 'is-loading' ) ) {
			return;
		}

		$this.addClass( 'is-loading' );
		$this.find('img').animate( { 'opacity': 0 }, 150 );

		$.ajax({
			url: gptechats.ajax_url,
			type: 'post',
			data: {
			action: 'gpte_chats_handler',
			gpte_nonce: gptechats.ajax_nonce,
			handler: 'duplicate_chat',
			language: gptechats.language,
			chat_id: dataTemplateId,
			},
			success: function( res ) {

			console.log(res);

			$this.removeClass( 'is-loading' );
			$this.find('img').animate( { 'opacity': 1 }, 150 );

			if ( res[ 'success' ] === 'true' || res[ 'success' ] === true ) {
				window.location.reload();
			}
			},
			error: function( errorThrown ) {
			$this.removeClass( 'is-loading' );
			console.log( errorThrown );
			}
		});
		}

    });


  });
</script>