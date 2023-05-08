<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Gpteverest_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_Run
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Run{

	/**
	 * Our Gpteverest_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
		
		// Execute specific objects
		GPTE()->openai->execute();
		GPTE()->chats->execute();
		GPTE()->integrations->execute();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){

		add_shortcode( 'gpteverest', array( $this, 'add_gpte_assistant' ), 100 );
	
		//TEST
		add_action( 'init', array( $this, 'test_functions' ), 20 );

		add_action( 'plugin_action_links_' . GPTE_PLUGIN_BASE, array( $this, 'plugin_action_links') );
		add_filter( 'admin_footer_text', array( $this, 'display_footer_information' ), 50, 2 );

		add_action( 'init', array( $this, 'register_custom_post_types' ), 20 );

		add_action( 'admin_menu', array( $this, 'add_user_submenu' ), 150 );
		add_filter( 'submenu_file', array( $this, 'filter_active_gpte_submenu_page' ), 150, 2 );
		add_action( 'admin_init', array( $this, 'maybe_redirect_gpte_submenu_items' ), 150, 2 );
		add_filter( 'gpte/admin/settings/menu_data', array( $this, 'add_main_settings_tabs' ), 10 );
		add_action( 'gpte/admin/settings/menu/place_content', array( $this, 'add_main_settings_content' ), 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts_and_styles' ), 20 );

		add_action( 'wp_ajax_gpte_send_message', array( $this, 'gpte_send_message_callback' ), 20 );

		// Validate settings
		add_action( 'admin_init',  array( $this, 'gpte_save_settings' ) );

		// Fetch chat
		add_action( 'wp_ajax_gpte_fetch_messages', array( $this, 'gpte_fetch_messages_callback' ), 20 );

		// Fetch agen ts
		add_action( 'wp_ajax_gpte_fetch_agents', array( $this, 'gpte_fetch_agents_callback' ), 20 );

		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu_items' ), 100, 1 );
		
	}

	public function test_functions(){

		if( ! current_user_can( 'manage_options' ) ){
			return;
		}

		if( isset( $_GET['prompt'] ) ){
			$prompt = GPTE()->prompts->get_master_prompt();
			$prompt = htmlspecialchars( $prompt );
			$prompt = str_replace( PHP_EOL, '<br>', $prompt );
			echo($prompt);
			exit;
		}

		if( isset( $_GET['integrations'] ) ){
			$integrations = GPTE()->integrations->get_integrations();
			echo json_encode( $integrations );
			exit;
		}

		if( isset( $_GET['commands'] ) ){
			$commands = GPTE()->integrations->get_commands();
			echo count( $commands );
			exit;
		}

		if( isset( $_GET['openaiprompt'] ) ){
			$commands = GPTE()->prompts->prepare_prompt_for_openai( $_GET['openaiprompt'] );
			header('Content-Type: application/json');
			echo json_encode( $commands );
			exit;
		}

		if( isset( $_GET['parent'] ) ){
			$parent_chat = GPTE()->chats->get_chat_by_agent( 9800, array( 'max_level' => 1 ) );

			$args = array(
				'post_type' => 'gptechats',
				'post_status' => 'publish',
				'post_parent' => $parent_chat->ID,
			);
			$posts = new WP_Query( $args );
			header('Content-Type: application/json');
			echo json_encode( $parent_chat->ID );
			exit;
		}

		if( isset( $_GET['json'] ) ){
			$jsonstring = '{"thoughts":{"text":"Now that we have retrieved the post content, we can use a regular expression to replace the submit button text within the post content. We can use the wp_call_wordpress_function command to call the wp_update_post() function to update the post content with the new submit button text.","reasoning":"Using a regular expression to find and replace the submit button text within the post content is the most efficient way to update only the submit button text. We can use the wp_update_post() function to update the post content with the new submit button text.","plan":"- Use a regular expression to replace the submit button text within the post content\n- Call wp_call_wordpress_function command with function_name as wp_update_post, function_definition as \'wp_update_post( array $postarr, bool $wp_error = false )\', and function_parameters as array(\'ID\' => 9793, \'post_content\' => \'updated post content\')","criticism":"None","speak":"Now, I will use a regular expression to replace the submit button text within the post content, and then update the post content."},"command":{"name":"wp_call_wordpress_function","args":{"function_name":"wp_update_post","function_definition":"wp_update_post( array $postarr, bool $wp_error = false )","function_parameters":{"ID":9793,"post_content":"<style>\n  form {\n    background-color: #143349;\n    color: #2cbcb8;\n    padding: 20px;\n    border-radius: 5px;\n  }\n  input[type=text], input[type=email], textarea {\n    width: 100%;\n    padding: 12px;\n    border: none;\n    border-radius: 4px;\n    margin-top: 6px;\n    margin-bottom: 16px;\n    resize: vertical;\n    background-color: #f8f8f8;\n    color: #143349;\n  }\n  input[type=submit] {\n    background-color: #2cbcb8;\n    color: #143349;\n    padding: 12px 20px;\n    border: none;\n    border-radius: 4px;\n    cursor: pointer;\n  }\n  input[type=submit]:hover {\n    background-color: #143349;\n    color: #2cbcb8;\n  }\n<\/style>\n<form>\n  <label for=\"name\">Name:<\/label><br>\n  <input type=\"text\" id=\"name\" name=\"name\"><br>\n  <label for=\"email\">Email:<\/label><br>\n  <input type=\"email\" id=\"email\" name=\"email\"><br>\n  <label for=\"message\">Message:<\/label><br>\n  <textarea id=\"message\" name=\"message\"><\/textarea><br><br>\n  <input type=\"submit\" value=\"Join waitlist\">\n<\/form>"}}}';
			$commands = GPTE()->prompts->maybe_correct_json( $jsonstring );
			header('Content-Type: application/json');
			echo json_encode( $commands );
			exit;
		}

		if( isset( $_GET['exec_action'] ) ){
			$commands = GPTE()->integrations->safe_execute_command( 
				array( 'success' => false, 'msg' => '' ), 
				'wp_call_wordpress_function',
				array(
					'function_name' => 'get_post_type',
					'function_parameters' => array(
						'post_id' => 9792
					),
				)
			);
			header('Content-Type: application/json');
			echo json_encode( $commands );
			exit;
		}

		if( isset( $_GET['testerito'] ) ){
			ob_start();
			?>
This reminds you of these events from your past:\n[\"Assistant Reply: {\\n    'thoughts': {\\n        'text': 'I think the first step should be to gather information on potential business opportunities. I can use the \\\"google\\\" command to search for business ideas and analyze the results to determine which ones are worth pursuing. Additionally, I can use the \\\"browse_website\\\" command to research specific industries and companies.',\\n        'reasoning': 'Before I can develop and manage multiple businesses autonomously, I need to identify viable business opportunities. Researching potential business ideas and industries will help me make informed decisions.',\\n        'plan': '- Use \\\"google\\\" command to search for business ideas\\\\n- Use \\\"browse_website\\\" command to research specific industries and companies\\\\n- Analyze results to determine which business opportunities are worth pursuing',\\n        'criticism': 'I need to ensure that I am analyzing the search results and website information thoroughly to make informed decisions. I should also consider the potential risks and challenges associated with each business opportunity.',\\n        'speak': 'I will use the \\\"google\\\" command to search for business ideas and the \\\"browse_website\\\" command to research specific industries and companies to identify viable business opportunities.'\\n    },\\n    'command': {\\n        'name': 'google',\\n        'args': {\\n            'input': 'business ideas'\\n        }\\n    }\\n} \\nResult: Command google returned: [\\n    {\\n        'title': '19 Small Business Ideas For 2023 - Forbes Advisor',\\n        'href': 'https://www.forbes.com/advisor/business/small-business-ideas/',\\n        'body': '12. Home Cleaning. A home cleaning service business is an excellent idea for detail-oriented people who want to be solopreneurs or who want to grow to have a team. As a home cleaner, you go to ...'\\n    },\\n    {\\n        'title': '70 Small Business Ideas for Anyone Who Wants to Run Their Own ... - HubSpot',\\n        'href': 'https://blog.hubspot.com/sales/small-business-ideas',\\n        'body': '9. Etsy Shop Owner. Creating novelties by hand is a fun and unique way to start a small business, and you can easily sell them via Etsy. Whether you make jewelry, knitted comfort items, or even custom wigs, there\\\"s probably a market for your products and an Etsy buyer who\\\"s ready to purchase. Image Source.'\\n    },\\n    {\\n        'title': '26 Small Business Ideas Anyone Can Start in 2023 - Shopify',\\n        'href': 'https://www.shopify.com/blog/low-investment-business-ideas',\\n        'body': 'These 26 small business ideas make a great entry point for beginners, bootstrappers, or anyone with a busy schedule, and let you pick up a side business without having to drop everything else. Get your free Big List of Business Ideas. Looking to start a business but unsure what to sell? Check out our free Big List of Business Ideas with 100 ...'\\n    },\\n    {\\n        'title': '17 Unique Business Ideas for You To Try This Year - Shopify',\\n        'href': 'https://www.shopify.com/blog/unique-business-ideas',\\n        'body': '17 unique small business ideas for first-time entrepreneurs 1. Be the head chef of your own food truck. If you love cooking, you may have fantasized about one day opening your very own restaurant. But did you know that since 2016, growth in the mobile food industry has been outpacing growth of traditional restaurants?'\\n    },\\n    {\\n        'title': '50 Best Small-Business Ideas - NerdWallet',\\n        'href': 'https://www.nerdwallet.com/article/small-business/business-ideas',\\n        'body': '24. Craft brewery. Craft breweries, aka microbreweries, are booming in the United States—in fact, 98% of operating breweries in the U.S. are independently owned. So, if you\\\"ve been tinkering ...'\\n    },\\n    {\\n        'title': 'Top 30 Small Business Ideas for 2023 [Updated] | Oberlo',\\n        'href': 'https://www.oberlo.com/blog/business-ideas-that-make-money',\\n        'body': '30 best business ideas of 2023. If you have been asking yourself what business to start, then this list is for you. According to small business statistics, one of the biggest motivations for opening your own business is being your own boss.Owning a business gives you the freedom to work when, where, or how you want.'\\n    },\\n    {\\n        'title': '40 Best Startup Ideas to Make You Money in 2022 - NerdWallet',\\n        'href': 'https://www.nerdwallet.com/article/small-business/small-business-startup-ideas',\\n        'body': '3. Start a meal-prep business. By 2026, more than 217 million people in the U.S. will use online food delivery services. Tap into that market and start a meal-prep service to make people\\\"s lives ...'\\n    },\\n    {\\n        'title': '82 Best Business Ideas For New Entrepreneurs [2023 Edition]',\\n        'href': 'https://digital.com/business-ideas/',\\n        'body': 'Source: Clearvoice.com What Are Good B2B Business Services Ideas? 1. Online Bookkeeping. Just like so many other professions, bookkeeping has gone online. This is great news for many bookkeepers and accountants who feel trapped in the office environment and long for more personal freedom and the ability to work during their own hours.'\\n    }\\n] \\nHuman Feedback: GENERATE NEXT COMMAND JSON \"]\n\n
			<?php
			$html = ob_get_clean();

			$html = str_replace( "\\\\n", '<br>', $html );
			$html = str_replace( "\\n", '<br>', $html );

			echo $html;
			exit;
		}

	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Plugin action links.
	 *
	 * Adds action links to the plugin list table
	 *
	 * Fired by `plugin_action_links` filter.
	 *
	 * @access public
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array An array of plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . GPTE_SLUG ), __( 'Settings', 'gpte' ) );

		array_unshift( $links, $settings_link );

		$links['our_shop'] = sprintf( '<a href="%s" target="_blank" style="font-weight:700;color:#2cbcb8;">%s</a>', 'https://gpteverest.com/?utm_source=gpteverest&utm_medium=plugin-overview-shop-button&utm_campaign=GPTEverest%20Plugin', __( 'Visit us', 'gpte' ) );

		return $links;
	}

	/**
	 * Add footer information about our plugin
	 *
	 * @since 4.2.1
	 * @access public
	 *
	 * @param string The current footer text
	 *
	 * @return string Our footer text
	 */
	public function display_footer_information( $text ) {

		if( GPTE()->helpers->is_page( GPTE_SLUG ) ){
			$text = sprintf(
				__( '%1$s version %2$s', 'gpte' ),
				'<strong>' . GPTE_NAME . '</strong>',
				'<strong>' . GPTE_VERSION . '</strong>'
			);
		}

		return $text;
	}

	 /**
	 * ######################
	 * ###
	 * #### MENU TEMPLATE ITEMS
	 * ###
	 * ######################
	 */

	/**
	 * Add our custom admin user page
	 */
	public function add_user_submenu(){
		
		add_menu_page(
			__( GPTE_NAME, 'gpte' ),
			__( GPTE_NAME, 'gpte' ),
			GPTE()->settings->get_admin_cap( 'admin-add-menu-page-item' ),
			GPTE_SLUG,
			array( $this, 'render_admin_submenu_page' ) ,
			GPTE_PLUGIN_URL . 'core/includes/assets/img/gpteverest-logo-20x20.svg',
			'81.026'
		);

		/**
		 * Originally called within /core/includes/partials/gpte-page-display.php,
		 * but used here to re-validate the available menu items dynamically
		 */
		$menu_endpoints = apply_filters( 'gpte/admin/settings/menu_data', array() );
		if( is_array( $menu_endpoints ) && ! empty( $menu_endpoints ) ){
			foreach( $menu_endpoints as $endpoint_slug => $endpoint_data ){

				$sub_page_title = ( is_array( $endpoint_data ) ) ? $endpoint_data['label'] : $endpoint_data;

				add_submenu_page(
					GPTE_SLUG,
					__( $sub_page_title, 'gpte' ),
					__( $sub_page_title, 'gpte' ),
					GPTE()->settings->get_admin_cap( 'admin-add-submenu-page-item' ),
					GPTE_SLUG . '-' . sanitize_title( $endpoint_slug ),
					array( $this, 'render_admin_submenu_page' )
				);
			}
		}

		//Remove its duplicate sub menu item
		remove_submenu_page( GPTE_SLUG, GPTE_SLUG);

	}

	/**
	 * Mark our dynamic sub menu item as active
	 *
	 * @param string $submenu_file
	 * @param string $parent_file
	 * @return string The submenu item in case given
	 */
	public function filter_active_gpte_submenu_page( $submenu_file, $parent_file ){

		if( $parent_file === GPTE_SLUG ){
			if( isset( $_REQUEST['gptevrs'] ) && ! empty( $_REQUEST['gptevrs'] ) ){

				$sub_menu_slug = $_REQUEST['gptevrs'];

				/**
				 * Originally called within /core/includes/partials/gpte-page-display.php,
				 * but used here to re-validate the available menu items dynamically
				 */
				$menu_endpoints = apply_filters( 'gpte/admin/settings/menu_data', array() );
				if( is_array( $menu_endpoints ) && ! empty( $menu_endpoints ) ){

					//Set the parent slug in case a child item is given
					if( ! isset( $menu_endpoints[ $sub_menu_slug ] ) ){
						foreach( $menu_endpoints as $endpoint_slug => $endpoint_data ){

							// Skip non sub menus
							if( ! isset( $endpoint_data['items'] ) ){
								continue;
							}

							if( isset( $endpoint_data['items'][ $sub_menu_slug ] ) ){
								$sub_menu_slug = $endpoint_slug;
							}

						}
					}

				}

				$submenu_file = GPTE_SLUG . '-' . sanitize_title( $sub_menu_slug );
			}
		}

		return $submenu_file;
	}

	/**
	 * Maybe redirect a menu item from a submenu URL
	 *
	 * @return void
	 */
	public function maybe_redirect_gpte_submenu_items(){

		if( ! isset( $_GET['page'] ) ){
			return;
		}

		//shorten the circle if nothing was set.
		if( isset( $_GET['gptevrs'] ) ){
			return;
		}

		$page = $_GET['page'];
		$ident = GPTE_SLUG;

		//Only redirect if it differs
		if( $ident === $page ){
			return;
		}

		if( strlen( $page ) < strlen( $ident ) ){
			return;
		}

		if( substr( $page, 0, strlen( $ident ) ) !== $ident ){
			return;
		}

		$page_slug = str_replace( GPTE_SLUG, '', $page );

		$url = GPTE()->helpers->get_current_url( false );
		$redirect_uri = GPTE()->helpers->built_url( $url, array(
			'page' => GPTE_SLUG,
			'gptevrs' => sanitize_title( $page_slug ),
		) );

		wp_redirect( $redirect_uri );
		exit;

	}

	/**
	 * Render the admin submenu page
	 *
	 * You need the specified capability to edit it.
	 */
	public function render_admin_submenu_page(){
		if( ! current_user_can( GPTE()->settings->get_admin_cap('admin-submenu-page') ) ){
			wp_die( __( 'You do not have permision to view this.', 'gpte' ) );
		}

		$gpte_page = GPTE_PLUGIN_DIR . 'core/includes/partials/gpte-page-display.php';

		/*
		 * Filter the core display page
		 *
		 * @param $gpte_page The page template
		 */
		$gpte_page = apply_filters( 'gpte/admin/page_template_file', $gpte_page );

		if( file_exists( $gpte_page ) ){
			include( $gpte_page );
		}

	}

	/**
	 * Register all of our default tabs to our plugin page
	 *
	 * @param $tabs - The previous tabs
	 *
	 * @return array - Return the array of all available tabs
	 */
	public function add_main_settings_tabs( $tabs ){

		$tabs['home']           = __( 'Home', 'gpte' );
		$tabs['chats']      = array(
			'label' => __( 'Chats', 'gpte' ),
			'title' => __( 'All chats', 'gpte' ),
			'items' => array(
				'chats' => __( 'All Chats', 'gpte' ),
				'agents' => __( 'All Agents', 'gpte' ),
			),
		);
		$tabs['settings']   = array(
			'label' => __( 'Settings', 'gpte' ),
			'title' => __( 'Settings', 'gpte' ),
			//'items' => array(
			//	'settings'  		=> __( 'All Settings', 'gpte' ),
			//	'tools'  			=> __( 'Tools', 'gpte' ),
			//),
		);

		return $tabs;

	}

	/**
	 * Load the content for our plugin page based on a specific tab
	 *
	 * @param $tab - The currently active tab
	 */
	public function add_main_settings_content( $tab ){

		switch( $tab ){
			case 'home':
				include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/home.php' );
				break;
			case 'chats':
				if( isset( $_GET['chat_id'] ) && current_user_can( GPTE()->settings->get_admin_cap( 'chat-edit-single' ) ) ){

					$chat_id = intval( $_GET['chat_id'] );

					include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/chats-single.php' );
				} else {
					include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/chats.php' );
				}
				break;
			case 'agents':
				if( isset( $_GET['chat_id'] ) && current_user_can( GPTE()->settings->get_admin_cap( 'chat-edit-single' ) ) ){

					$chat_id = intval( $_GET['chat_id'] );

					include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/chats-single.php' );
				} else {
					include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/agents.php' );
				}
				break;
			case 'settings':
				include( GPTE_PLUGIN_DIR . 'core/includes/partials/tabs/settings.php' );
				break;
		}

	}

	// Register Custom Post Type
	public function register_custom_post_types() {

		$labels = array(
			'name'                  => _x( 'GPTE Chats', 'Post type general name', 'gpteverest' ),
			'singular_name'         => _x( 'GPTE Chat', 'Post type singular name', 'gpteverest' ),
			'menu_name'             => _x( 'GPTE Chats', 'Admin Menu text', 'gpteverest' ),
			'name_admin_bar'        => _x( 'GPTE Chat', 'Add New on Toolbar', 'gpteverest' ),
			'add_new'               => __( 'Add New', 'gpteverest' ),
			'add_new_item'          => __( 'Add New GPTE Chat', 'gpteverest' ),
			'new_item'              => __( 'New GPTE Chat', 'gpteverest' ),
			'edit_item'             => __( 'Edit GPTE Chat', 'gpteverest' ),
			'view_item'             => __( 'View GPTE Chat', 'gpteverest' ),
			'all_items'             => __( 'All GPTE Chats', 'gpteverest' ),
			'search_items'          => __( 'Search GPTE Chats', 'gpteverest' ),
			'parent_item_colon'     => __( 'Parent GPTE Chats:', 'gpteverest' ),
			'not_found'             => __( 'No GPTE Chats found.', 'gpteverest' ),
			'not_found_in_trash'    => __( 'No GPTE Chats found in Trash.', 'gpteverest' ),
			'featured_image'        => _x( 'GPTE Chat Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'gpteverest' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'gpteverest' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'gpteverest' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'gpteverest' ),
			'archives'              => _x( 'GPTE Chat archives', 'The post type archive label used in nav menus. Default "Post Archives".', 'gpteverest' ),
			'insert_into_item'      => _x( 'Insert into GPTE Chat', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'gpteverest' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this GPTE Chat', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'gpteverest' ),
			'filter_items_list'     => _x( 'Filter GPTE Chats list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list".', 'gpteverest' ),
			'items_list_navigation' => _x( 'GPTE Chats list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation".', 'gpteverest' ),
			'items_list'            => _x( 'GPTE Chats list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list".', 'gpteverest' ),
		);

		$supports = array(
			'title',			// post title
			'editor',			// post content
			//'author',			// post author
			//'thumbnail',		// featured image
			//'excerpt',			// post excerpt
			//'custom-fields',	// custom fields
			//'comments',			// post comments
			//'revisions',		// post revisions
			//'post-formats',		// post formats
		);

		$args = array(
			'labels'				=> $labels,
			'supports' 				=> $supports,
			'public'				=> true, //change false
			'publicly_queryable'	=> true, //change false
			'show_ui'				=> true, //change false
			'show_in_menu'			=> true, //change false
			'query_var'				=> true,
			'rewrite'				=> array( 'slug' => 'gptechats', 'with_front' => false ),
			'capability_type'		=> 'post',
			'has_archive'			=> false,
			'hierarchical'			=> true,
			'menu_position'			=> 4.6,
			'exclude_from_search'	=> true,
			'supports'				=> array( 'title', 'editor' ),
		);

		register_post_type( 'gptechats', $args );

	}

	/**
	 * Enqueue the backend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the backend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_backend_scripts_and_styles() {

		if( GPTE()->helpers->is_page( GPTE_SLUG ) && is_admin() ) {

			$is_dev_mode = GPTE()->helpers->is_dev();
			$ajax_nonce = wp_create_nonce( md5( GPTE_SLUG ) );
			$language = get_locale();
			$current_ID = ( isset( $_GET['chat_id'] ) && $_GET['chat_id'] ) ? intval( $_GET['chat_id'] ) : 0;
			$agent_id = ( isset( $_GET['agent_id'] ) && $_GET['agent_id'] ) ? intval( $_GET['agent_id'] ) : 0;
			$is_chat = ( get_post_type( $current_ID ) === 'gptechats' ) ? 'yes' : 'no';

			wp_enqueue_style( 'gpte-google-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;700&family=Poppins:wght@500&display=swap', array(), null );

			wp_enqueue_style( 'gpte-admin-styles', GPTE_PLUGIN_URL . 'core/includes/assets/dist/css/admin-styles' . ( $is_dev_mode ? '' : '.min' ) . '.css', array(), GPTE_VERSION, 'all' );

			wp_enqueue_script( 'jquery-ui-sortable');
			wp_enqueue_editor();
			wp_enqueue_media();

			wp_enqueue_script( 'gpte-admin-vendors', GPTE_PLUGIN_URL . 'core/includes/assets/dist/js/admin-vendor' . ( $is_dev_mode ? '' : '.min' ) . '.js', array( 'jquery' ), GPTE_VERSION, true );
			wp_enqueue_script( 'gpte-admin-scripts', GPTE_PLUGIN_URL . 'core/includes/assets/dist/js/admin-scripts' . ( $is_dev_mode ? '' : '.min' ) . '.js', array( 'jquery' ), GPTE_VERSION, true );

			wp_localize_script( 'gpte-admin-scripts', 'gpte', array(
				'ajax_url'   		=> admin_url( 'admin-ajax.php' ),
				'ajax_nonce' 		=> $ajax_nonce,
				'language' 			=> $language,
				'is_chat'			=> $is_chat,
				'current_chat'		=> $current_ID,
				'agent_id'		=> $agent_id,
				'automode'			=> GPTE()->chats->is_automode(),
			));

			//Setup for the no-conflict mode
			if ( is_admin() ){
				add_action( 'wp_print_scripts', array( $this, 'no_conflict_mode_scripts' ), 1000 );
				add_action( 'admin_print_footer_scripts', array( $this, 'no_conflict_mode_scripts' ), 9 );
	
				add_action( 'wp_print_styles', array( $this, 'no_conflict_mode_styles' ), 1000 );
				add_action( 'admin_print_styles', array( $this, 'no_conflict_mode_styles' ), 1 );
				add_action( 'admin_print_footer_scripts', array( $this, 'no_conflict_mode_styles' ), 1 );
				add_action( 'admin_footer', array( $this, 'no_conflict_mode_styles' ), 1 );
			}
		}
		
	}


	/**
	 * The callback function for gpte_send_message
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function gpte_send_message_callback() {
		check_ajax_referer( md5( GPTE_SLUG ), 'gpte_nonce' );

		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( $_REQUEST['message'] ) : '';
		$chat_id = isset( $_REQUEST['chat_id'] ) ? intval( $_REQUEST['chat_id'] ) : 0;

		$message = stripslashes( $message );

		$response = GPTE()->chats->process_message( $chat_id, array( 'role' => 'user', 'content' => $message ), array( 'add_command_details' => true ) );

		if( $response['success'] ){
			wp_send_json( $response );
		} else {
			wp_send_json( $response );
		}

		die();
	}

	/**
	 * The callback function for gpte_send_message
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function gpte_fetch_messages_callback() {
		check_ajax_referer( md5( GPTE_SLUG ), 'gpte_nonce' );

		$chat_id = isset( $_REQUEST['chat_id'] ) ? intval( $_REQUEST['chat_id'] ) : 0;
		$response = array( 
			'success' => false,
			'msg' => '',
			'data' => array(),
		);

		if( GPTE()->chats->get_chat_type( $chat_id ) === 'chat' ){
			$default_messages = GPTE()->chats->get_starter_messages();
		} else {
			$default_messages = array();
		}
		
		$messages = GPTE()->chats->get_chat_messages( $chat_id );

		//Append the default messages here to save tokens
		$messages = array_merge( $default_messages, $messages );

		if ( ! empty( $chat_id ) ) {
			$response['success'] = true;
			$response['msg'] = __( 'The value was successfully filled.', 'gpteverest' );
			$response['data']['messages'] = $messages;
		} else {
			$response['msg'] = __( 'No messages found.', 'gpteverest' );
		}

		if( $response['success'] ){
			wp_send_json( $response );
		} else {
			wp_send_json( $response );
		}

		die();
	}

	/**
	 * The callback function for gpte_fetch_agents
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function gpte_fetch_agents_callback() {
		check_ajax_referer( md5( GPTE_SLUG ), 'gpte_nonce' );

		$chat_id = isset( $_REQUEST['chat_id'] ) ? intval( $_REQUEST['chat_id'] ) : 0;
		$response = array( 
			'success' => false,
			'msg' => '',
			'data' => array(),
		);

		$agents = GPTE()->chats->get_chat_agents( $chat_id );

		if ( ! empty( $chat_id ) ) {
			$response['success'] = true;
			$response['msg'] = __( 'The agents have been successfully retrieved.', 'gpteverest' );
			$response['data']['agents'] = $agents;
		} else {
			$response['msg'] = __( 'No agents found.', 'gpteverest' );
		}

		if( $response['success'] ){
			wp_send_json( $response );
		} else {
			wp_send_json( $response );
		}

		die();
	}

	/**
	 * Add a new menu item to the WordPress topbar
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param	object $admin_bar The WP_Admin_Bar object
	 *
	 * @return	void
	 */
	public function add_admin_bar_menu_items( $admin_bar ) {

		$admin_bar->add_menu( array(
			'id'		=> 'gpteverest-id', // The ID of the node.
			'title'		=> __( 'GPTEverest', 'gpteverest' ), // The text that will be visible in the Toolbar. Including html tags is allowed.
			'parent'	=> false, // The ID of the parent node.
			'href'		=> '#', // The ‘href’ attribute for the link. If ‘href’ is not set the node will be a text node.
			'group'		=> false, // This will make the node a group (node) if set to ‘true’. Group nodes are not visible in the Toolbar, but nodes added to it are.
			'meta'		=> array(
				'title'		=> __( 'GPTEverest', 'gpteverest' ), // The title attribute. Will be set to the link or to a div containing a text node.
				'target'	=> '_blank', // The target attribute for the link. This will only be set if the ‘href’ argument is present.
				'class'		=> 'gpteverest-class', // The class attribute for the list item containing the link or text node.
				'html'		=> false, // The html used for the node.
				'rel'		=> false, // The rel attribute.
				'onclick'	=> false, // The onclick attribute for the link. This will only be set if the ‘href’ argument is present.
				'tabindex'	=> false, // The tabindex attribute. Will be set to the link or to a div containing a text node.
			),
		));

		$admin_bar->add_menu( array(
			'id'		=> 'gpteverest-sub-id',
			'title'		=> __( 'GPTEverest', 'gpteverest' ),
			'parent'	=> 'gpteverest-id',
			'href'		=> '#',
			'group'		=> false,
			'meta'		=> array(
				'title'		=> __( 'GPTEverest', 'gpteverest' ),
				'target'	=> '_blank',
				'class'		=> 'gpteverest-sub-class',
				'html'		=> false,    
				'rel'		=> false,
				'onclick'	=> false,
				'tabindex'	=> false,
			),
		));

	}

	public function add_gpte_assistant( $atts = array(), $content = '' ){

		$chat_id = ( isset( $_GET['chat_id'] ) && $_GET['chat_id'] ) ? intval( $_GET['chat_id'] ) : 0;

		$name = 'Bot';
		$title = get_the_title( $chat_id );
		if( ! empty( $title ) ){
			$name = $title;
		}

		//Bail
		if( empty( $chat_id ) ){
			return '';
		}

		ob_start();
		?>
<div class="gpte-wrapper">
	<div class="gpte-columns">
		<div class="message-details">
			<h2 class="message-details-header"><?php echo __( 'Message details', 'gpteverest' ); ?></h2>
			<hr class="message-details-line" />
			<div id="message-data"></div>
		</div>
		<div class="gpte-chat-wrapper">
			<div id="chat" class="chat"></div>
			<div id="thinking" class="chat__message chat__message--bot thinking">Agent is thinking...</div>
			<div class="chat__wrapper">
				<form id="chatForm" class="chat__form">
					<input type="text" id="chat-input" class="chat-input" autocomplete="off">
					<input type="hidden" id="thread" value="0">
					<button type="submit">Send</button>
				</form>
			</div>
		</div>
		<div class="chat__details">
			<div class="chat__details--main">
				<h2 class="message-details-header"><?php echo __( 'Main chat', 'gpteverest' ); ?></h2>
				<hr class="message-details-line" />
				<div id="chat-btn-<?php echo $chat_id; ?>" class="chats__button gpte-btn gpte-btn--sm gpte-btn--secondary w-100 current" data-chat-id="<?php echo $chat_id; ?>"><?php echo $name; ?></div>
			</div>
			<div class="chat__details--chats">
				<h2 class="message-details-header"><?php echo __( 'Chat Agents', 'gpteverest' ); ?></h2>
				<hr class="message-details-line" />
				<div id="agents-wrapper"></div>
			</div>
		</div>
	</div>

	<div class="gpte-controls">
		<div class="gpte-controls-automode">
			<div class="mb-2">
				<strong>Automode</strong>
			</div>
			<div class="gpte-toggle gpte-toggle--on-off">
				<?php if( GPTE()->chats->is_automode() ) : ?>
					<input type="checkbox" id="automode" name="automode" class="gpte-toggle__input" checked>
				<?php else : ?>
					<input type="checkbox" id="automode" name="automode" class="gpte-toggle__input">
				<?php endif; ?>
				<label class="gpte-toggle__btn" for="automode"></label>
			</div>
			<p>
				<small>
				Automode allows you to let the agent run without you permitting an action. Use with care.
				<br />
				You can disable the automode at any time. which causes the next message to require permission again.
				</small>
			</p>
		</div>
	</div>
</div>

		<?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * ######################
	 * ###
	 * #### NO CONFLICT MODE
	 * ###
	 * ######################
	 */

	/**
	 * Define the required no-conflict mode scripts.
	 *
	 * @since  5.2.2
	 * @access public
	 * @global $wp_scripts
	 *
	 */
	public function no_conflict_mode_scripts(){

		//Bail if not a GPTEverest page
	    if( ! GPTE()->helpers->is_page( GPTE_SLUG ) ) {
			return;
		}

		global $wp_scripts;

		$required_scripts = GPTE()->settings->get_no_conflict_scripts_styles( 'scripts' );

		//Queue only required scripts
		$queue = array();
		foreach( $wp_scripts->queue as $object ){
			if( in_array( $object, $required_scripts ) ){
				$queue[] = $object;
			}
		}
		$wp_scripts->queue = $queue;

		//Add possible dependencies
		$dependencies = array();
		foreach( $required_scripts as $script ){
			$deps = isset( $wp_scripts->registered[ $script ] ) && is_array( $wp_scripts->registered[ $script ]->deps ) ? $wp_scripts->registered[ $script ]->deps : array();
			foreach( $deps as $dep ){
				if( ! in_array( $dep, $required_scripts ) && ! in_array( $dep, $dependencies ) ){
					$dependencies[] = $dep;
				}
			}
		}

		$required_objects = array_merge( $required_scripts, $dependencies );

		//Register only required scripts
		$registered = array();
		foreach( $wp_scripts->registered as $script_name => $script_registration ){
			if( in_array( $script_name, $required_objects ) ){
				$registered[ $script_name ] = $script_registration;
			}
		}
		$wp_scripts->registered = $registered;
    }

	/**
	 * Define the required no-conflict mode scripts.
	 *
	 * @since  5.2.2
	 * @access public
	 * @global $wp_styles
	 *
	 */
	public function no_conflict_mode_styles(){

		//Bail if not a GPTEverest page
	    if( ! GPTE()->helpers->is_page( GPTE_SLUG ) ) {
			return;
		}

		global $wp_styles;

		$required_styles = GPTE()->settings->get_no_conflict_scripts_styles( 'styles' );

		//Queue only required styles
		$queue = array();
		foreach( $wp_styles->queue as $object ){
			if( in_array( $object, $required_styles ) ){
				$queue[] = $object;
			}
		}
		$wp_styles->queue = $queue;

		//Add possible dependencies
		$dependencies = array();
		foreach( $required_styles as $style ){
			$deps = isset( $wp_styles->registered[ $style ] ) && is_array( $wp_styles->registered[ $style ]->deps ) ? $wp_styles->registered[ $style ]->deps : array();
			foreach( $deps as $dep ){
				if( ! in_array( $dep, $required_styles ) && ! in_array( $dep, $dependencies ) ){
					$dependencies[] = $dep;
				}
			}
		}

		$required_objects = array_merge( $required_styles, $dependencies );

		//Register only required styles
		$registered = array();
		foreach( $wp_styles->registered as $style_name => $style_registration ){
			if( in_array( $style_name, $required_objects ) ){
				$registered[ $style_name ] = $style_registration;
			}
		}
		$wp_styles->registered = $registered;
    }

	/*
     * Functionality to save the main settings of the settings page
     */
	public function gpte_save_settings(){

        if( ! is_admin() || ! GPTE()->helpers->is_page( GPTE_SLUG ) ){
			return;
		}

		if( ! isset( $_POST['gpte_settings_submit'] ) ){
			return;
		}

		$settings_nonce_data = GPTE()->settings->get_nonce();

		if ( ! check_admin_referer( $settings_nonce_data['action'], $settings_nonce_data['arg'] ) ){
			return;
		}

		if( ! GPTE()->helpers->current_user_can( GPTE()->settings->get_admin_cap( 'gpte-save-settings' ), 'gptepro-page-settings-save' ) ){
			return;
		}

		$current_url = GPTE()->helpers->get_current_url();

		GPTE()->settings->save_settings( $_POST );

		wp_redirect( $current_url );
		exit;

    }

}
