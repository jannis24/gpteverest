<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GPTE_Integrations_wordpress Class
 *
 * This class integrates all WordPress related features and endpoints
 *
 * @since 1.0.0
 */
class GPTE_Integrations_wordpress {

    public function is_active(){
        return true;
    }

    public function get_details(){
        return array(
            'name' => 'WordPress',
            'icon' => 'assets/img/icon-wordpress.svg',
            'resources' => array(
				__( 'Access to all WordPress functions.', 'gpteverest' )
			),
        );
    }

}
