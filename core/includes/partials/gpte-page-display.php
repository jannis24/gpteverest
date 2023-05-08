<?php
/**
 * Main Template
 */

$heading = '';
$current_content = '';
$plugin_name = GPTE_NAME;

/**
 * Filter the menu tab items. You can extend here your very own tabs
 * as well.
 * Our default endpoints are declared in
 * core/includes/classes/class-gpteverest-run.php
 */
$menu_endpoints = apply_filters( 'gpte/admin/settings/menu_data', array() );

if( isset( $_GET['gptevrs'] ) && $_GET['gptevrs'] != 'home' ){

    $active_val = sanitize_title( $_GET['gptevrs'] );
    /**
     * Filter the global plugin admin capability again to create an
     * independent capability possibility system for the element settings
     */
    if( current_user_can( apply_filters( 'gpte/admin/settings/menu/page_capability', GPTE()->settings->get_admin_cap( 'gpte-page-settings' ), $active_val ) ) ){
        /**
         * The following hook gives you the possibility to
         * output custom content on the specified page with the filter
         *
         * @hook  gpte/admin/settings/menu_data
         */

        //Buffer for avoiding errors
        ob_start();
            do_action( 'gpte/admin/settings/menu/place_content', $active_val );
        $current_content = ob_get_clean();

        /**
         * Possibility to filter the content after
         * creating its output
         */
        $current_content = apply_filters( 'gpte/admin/settings/menu/filter_content', $current_content, $active_val );
    }

} else {
	$active_val      = 'home';

	ob_start();
        do_action( 'gpte/admin/settings/menu/place_content', $active_val );
    $current_content = ob_get_clean();

    $current_content = GPTE()->helpers->validate_local_tags( $current_content );
}

if( is_array( $menu_endpoints ) ){
	foreach( $menu_endpoints as $hook_name => $data ){

        $html_title = '';

        if( is_array( $data ) ){

            if( isset( $data['label'] ) ){
                $title = $data['label'];
            } else {
                $title = __( 'Undefined', 'gpte' );
            }

            if( isset( $data['title'] ) ){
                $html_title = 'title="' . $data['title'] . '"';
            }

        } else {
            $title = $data;
        }

		/**
		 * Filter the global plugin admin capability again to create an
		 * independend capability possibility system for the element settings
		 */
		if( current_user_can( apply_filters( 'gpte/admin/settings/menu/page_capability', GPTE()->settings->get_admin_cap( 'gpte-page-settings' ), $active_val ) ) ){

			/**
			 * Hook for Filterinng the title of a specified plugin file
			 */
			$title = apply_filters( 'gpte/admin/settings/element/filter_title', $title, $hook_name );

            $has_dropdown = false;

            if(
                (
                    is_array( $data )
                    && isset( $data['items'] )
                    && is_array( $data['items'] )
                    && count( $data['items'] ) > 1
                )
                ||
                (
                    is_array( $data )
                    && isset( $data['items'] )
                    && is_array( $data['items'] )
                    && count( $data['items'] ) <= 1
                    && ! isset( $data['items'][ $hook_name ] )
                )
            ){
                $has_dropdown = true;
            }

            $dd_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="9" fill="none" class="ml-1">
                <defs></defs>
                <path stroke="#0E0A1D" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l7 7 7-7"></path>
            </svg>';

			if( $active_val == $hook_name || $has_dropdown && isset( $data['items'][ $active_val ] ) ){
                $heading .= '<li class="active' . ( $has_dropdown ? ' has-dropdown' : '' ) . '"><a class="gpte-setting-single-tab active ' . $hook_name . '" ' . $html_title . ' href="?page=' . GPTE_SLUG . '&gptevrs=' . $hook_name . '">' . $title . ( $has_dropdown ? ' ' . $dd_svg : '' ) . '</a>';
			} else {
				$heading .= '<li' . ( $has_dropdown ? ' class="has-dropdown"' : '' ) . '><a class="gpte-setting-single-tab ' . $hook_name . '" ' . $html_title . ' href="?page=' . GPTE_SLUG . '&gptevrs=' . $hook_name . '">' . $title . ( $has_dropdown ? ' ' . $dd_svg : '' ) . '</a>';
            }

            if( $has_dropdown ){

                $heading .= '<ul>';

                foreach( $data['items'] as $sub_menu_name => $sub_menu_title ){

                    if( $active_val == $sub_menu_name ){
                        $heading .= '<li class="active"><a class="gpte-setting-single-tab active ' . $sub_menu_name . '" href="?page=' . GPTE_SLUG . '&gptevrs=' . $sub_menu_name . '">' . $sub_menu_title . '</a></li>';
                    } else {
                        $heading .= '<li><a class="gpte-setting-single-tab ' . $sub_menu_name . '" href="?page=' . GPTE_SLUG . '&gptevrs=' . $sub_menu_name . '">' . $sub_menu_title . '</a></li>';
                    }

                }

                $heading .= '</ul>';

            }

            $heading .= '</li>';
		}

	}
} else {
	$heading = '<li class="active"><a class="gpte-setting-single-tab" href="?page=' . GPTE_SLUG . '">' . __( $subs_origin['home'], 'gpte' ) . '</a></li>';
}

?>

<div class="gpte">
    <div class="gpte-header">
        <div class="gpte-container d-flex align-items-center justify-content-between">
            <div class="gpte-header__logo d-flex align-items-center">
                <img alt="<?php echo __( 'The GPTEverest logo', 'gpte' ); ?>" src="<?php echo GPTE_PLUGIN_URL . 'core/includes/assets/img/gpteverest-logo-min.svg'; ?>" />
                <div class="gpte-header__logo-text">
                    <?php echo GPTE_NAME; ?>
                </div>
            </div>
            <div class="gpte-header__exit">
                <a 
                    href="<?php echo get_dashboard_url(); ?>"
                    data-tippy=""
                    data-tippy-content="<?php echo __( 'Go back to WordPress', 'gpte' ); ?>"
                >
                    <img alt="<?php echo __( 'The back to WordPress icon', 'gpte' ); ?>" src="<?php echo GPTE_PLUGIN_URL . 'core/includes/assets/img/exit.svg'; ?>" />
                </a>
            </div>
        </div>
    </div>
    <!-- ./gpte-header -->
    <div class="gpte-menu">
        <div class="gpte-container">
            <ul class="gpte-menu__nav">
                <?php echo $heading; ?>
            </ul>
        </div>
    </div>
    <!-- /.gpte-menu -->

    <div class="gpte-main">
        <?php echo $current_content; ?>
    </div>

</div>