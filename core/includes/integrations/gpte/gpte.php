<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * GPTE_Integrations_gpte Class
 *
 * This class integrates all GPTE related features and endpoints
 *
 * @since 1.0.0
 */
class GPTE_Integrations_gpte {

	public function is_active() {
		return true;
	}

	public function get_details() {
		return array(
			'name' => 'GPTEverest',
			'icon' => 'assets/img/icon-gpte.svg',
			'resources' => array(
				__( 'GPT powered Agents for delegation of simple tasks.', 'gpteverest' ),
				__( 'Access to ChatGPT.', 'gpteverest' ),
			),
		);
	}

}

