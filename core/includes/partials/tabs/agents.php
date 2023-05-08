<?php

$chats_nonce = GPTE()->settings->get_nonce();
$clear_form_url = GPTE()->helpers->get_current_url( true, true );
$clean_url = GPTE()->helpers->get_current_url( false, true );

?>
<div class="gpte-container">
  <div class="gpte-title-area mb-4">
    <div class="gpte-title-area-wrapper d-flex mb-4">
      <h1 class="mb-0"><?php echo __( 'Agents', 'gpte' ); ?></h1>
    </div>
    
    <p>
      <?php echo sprintf(__( 'Agents are dynamically employed assistants by your AI chat assistant. Those agents will take care of tasks that have been assigned to by one of your chats. To learn more about agents, please visit <a class="text-secondary" title="Visit GPTEverest website" href="%s" target="_blank">our website</a>.', 'gpte' ), 'https://gpteverest.com'); ?>
    </p>
  </div>

  <?php 
    
    // Creating an instance
    $table = GPTE()->chats->get_agents_lists_table_class();
    // Prepare table
    $table->prepare_items();
    // Display table
    $table->display();

  ?>

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


  });
</script>