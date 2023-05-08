<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * GPTE_Integrations_sql Class
 *
 * This class integrates all SQL related features and endpoints
 *
 * @since 1.0.0
 */
class GPTE_Integrations_sql {

	public function is_active() {
		return true;
	}

	public function get_details() {
		return array(
			'name' => 'SQL',
			'icon' => 'assets/img/icon-sql.svg',
			'resources' => array(
				__( 'SQL Database Access to perform queries.', 'gpteverest' )
			),
		);
	}

}

