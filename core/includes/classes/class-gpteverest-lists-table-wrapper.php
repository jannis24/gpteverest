<?php

/**
 * GPTE_WP_List_Table_Wrapper Class
 *
 * This class contains a wrapper for the extended WP_List_Table class
 *
 * @since 1.0.0
 */

/**
 * The WP_List_Table wrapper class of the plugin.
 *
 * @since 1.0.0
 * @package GPTE
 * @author Jannis Thuemmig
 */
class GPTE_WP_List_Table_Wrapper {

	public function new_list( $args = array() ){
        $return = null;

		$list_table_class_file = GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-lists-table.php';

        if( 
			! class_exists( 'GPTE_WP_List_Table' )
		 	&& file_exists( $list_table_class_file ) 
		) {
			include( $list_table_class_file );
		}

		$return = new GPTE_WP_List_Table( $args );

        return $return;
    }

}
