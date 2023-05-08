<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Gpteverest' ) ) :

	/**
	 * Main Gpteverest Class.
	 *
	 * @package		GPTE
	 * @subpackage	Classes/Gpteverest
	 * @since		1.0.0
	 * @author		Jannis Thuemmig
	 */
	final class Gpteverest {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Gpteverest
		 */
		private static $instance;

		/**
		 * GPTE helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gpteverest_Helpers
		 */
		public $helpers;

		/**
		 * GPTE settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gpteverest_Settings
		 */
		public $settings;

		/**
		 * GPTE lists object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|GPTE_WP_List_Table_Wrapper
		 */
		public $lists;

		/**
		 * GPTE OpenAI object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gpteverest_OpenAI
		 */
		public $openai;

		/**
		 * GPTE prompts object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|GPTEverest_Prompts
		 */
		public $prompts;

		/**
		 * GPTE chats object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gpteverest_Chats
		 */
		public $chats;

		/**
		 * GPTE integrations object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|GPTE_Pro_Integrations
		 */
		public $integrations;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'gpteverest' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'gpteverest' ), '1.0.0' );
		}

		/**
		 * Main Gpteverest Instance.
		 *
		 * Insures that only one instance of Gpteverest exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Gpteverest	The one true Gpteverest
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Gpteverest ) ) {
				self::$instance					= new Gpteverest;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Gpteverest_Helpers();
				self::$instance->settings		= new Gpteverest_Settings();
				self::$instance->lists			= new GPTE_WP_List_Table_Wrapper();
				self::$instance->openai			= new Gpteverest_OpenAI();
				self::$instance->prompts		= new GPTEverest_Prompts();
				self::$instance->chats			= new Gpteverest_Chats();
				self::$instance->integrations	= new GPTE_Pro_Integrations();

				//Fire the plugin logic
				new Gpteverest_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'gpte/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-helpers.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-settings.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-lists-table-wrapper.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-lists-table.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-openai.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-prompts.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-prompt.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-chats.php';
			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-integrations.php';

			require_once GPTE_PLUGIN_DIR . 'core/includes/classes/class-gpteverest-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'gpteverest', FALSE, dirname( plugin_basename( GPTE_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.