<?php

$chat_title = get_the_title( $chat_id );

?>
<div class="gpte-container">
	<div class="gpte-title-area mb-4">
		<h2><?php echo $chat_title; ?></h2>
		<p><?php echo sprintf( __( 'For more information, please visit <a title="Our homepage" href="%s" target="_blank">our homepage</a>.', 'gpte' ), 'https://gpteverest.com/' ); ?></p>
  </div>
	<div id="gpte-chat">
		<?php echo do_shortcode( '[gpteverest]' ) ?>
	</div>
</div>