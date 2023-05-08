<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Gpteverest_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_Settings
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Settings{

	/**
	 * The admin capability
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $admin_cap;

	/**
	 * The default settings
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $default_settings;

	/**
	 * The integration dependencies
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $default_integration_dependencies;

	/**
	 * The OpenAI token limit
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $openai_token_limit;

	/**
	 * The no conflict scripts and styles
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $no_conflict_scripts_styles;

	/**
	 * Our Gpteverest_Settings constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->admin_cap = 'manage_options';
		$this->default_settings     = $this->load_default_settings();
		$this->default_integration_dependencies = array(
            'helpers',
            'commands',
        );
		$this->openai_token_limit = $this->load_token_limit();
		$this->no_conflict_scripts_styles = array(
			'scripts' => array(
				//WordPress related
				'admin-bar',
				'common',
				'jquery-color',
				'utils',
				'svg-painter',
				'mce-view',
				'backbone',
				'editor',
				'jquery',
				'jquery-ui-autocomplete',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-droppable',
				'jquery-ui-tabs',
				'jquery-ui-accordion',
				'json2',
				'media-editor',
				'media-models',
				'media-upload',
				'media-views',
				'plupload',
				'plupload-flash',
				'plupload-html4',
				'plupload-html5',
				'quicktags',
				'rg_currency',
				'thickbox',
				'word-count',
				'wp-plupload',
				'wp-tinymce',
				'wp-tinymce-root',
				'wp-tinymce-lists',
				'wpdialogs-popup',
				'wplink',
				'wp-pointer',

				//GPTEverest related
				'gpte-admin-vendors',
				'gpte-admin-scripts',
			),
			'styles'    => array(
				//WordPress related
				'admin-bar', 
				'colors', 
				'ie', 
				'wp-admin', 
				'editor-style',
				'thickbox',
				'editor-buttons',
				'wp-jquery-ui-dialog',
				'media-views',
				'buttons',
				'wp-pointer',

				//GPTEverest related
				'gpte-admin-styles',
				'gpte-google-fonts',
			)
		);
	}

	/**
	 * Load the default settings for the main settings page
	 * of our plugin.
	 *
	 * @return array - an array of all available settings
	 */
	private function load_default_settings(){
		$fields = array(

			'gpte_openai_api_key' => array(
				'id'          => 'gpte_openai_api_key',
				'type'        => 'text',
				'label'       => __( 'OpenAI API key', 'gpte' ),
				'placeholder' => '',
				'description' => __( 'Add the OpenAI key of your OpenAI account. Make sure you have setup a paid account for the API.', 'gpte' )
			),

			'gpte_openai_api_model' => array(
				'id'          => 'gpte_openai_api_model',
				'type'        => 'text',
				'label'       => __( 'OpenAI API model', 'gpte' ),
				'placeholder' => '',
				'default_value' => 'gpt-3.5-turbo',
				'description' => __( 'By default, we use gpt-3.5-turbo', 'gpte' )
			),

			'gpte_openai_api_token_limit' => array(
				'id'          => 'gpte_openai_api_token_limit',
				'type'        => 'text',
				'label'       => __( 'OpenAI API token limit', 'gpte' ),
				'placeholder' => '',
				'default_value' => '4096',
				'description' => __( 'Define the token limit of your API plan.', 'gpte' )
			),

		);

		foreach( $fields as $key => $field ){
			$value = get_option( $key );

			$fields[ $key ]['value'] = $value;

			if( $fields[ $key ]['type'] == 'checkbox' ){
				if( empty( $fields[ $key ]['value'] ) || $fields[ $key ]['value'] == 'no' ){
					$fields[ $key ]['value'] = 'no';
				} else {
					$fields[ $key ]['value'] = 'yes';
				}
			} elseif( empty( $fields[ $key ]['value'] ) && ! empty( $fields[ $key ]['default_value'] ) ){
				$fields[ $key ]['value'] = $fields[ $key ]['default_value'];
			}
		}

		return apply_filters('gpte/settings/fields', $fields);
	}

	private function load_token_limit(){

		$token_limit = get_option( 'gpte_openai_api_token_limit' );
		if( empty( $token_limit ) ){
			$token_limit = 4096;
		}

		return $token_limit;
	}

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * Our admin cap handler function
	 *
	 * This function handles the admin capability throughout
	 * the whole plugin.
	 *
	 * $target - With the target function you can make a more precised filtering
	 * by changing it for specific actions.
	 *
	 * @param string $target - A identifier where the call comes from
	 * @return mixed
	 */
	public function get_admin_cap($target = 'main'){
		/**
		 * Customize the globally used capability for this plugin
		 *
		 * This filter is called every time the capability is needed.
		 */
		return apply_filters( 'gpte/admin/settings/capability', $this->admin_cap, $target );
	}

	/**
	 * Return the default integration depenencies
	 *
	 * @return array - the default integration depenencies
	 */
	public function get_default_integration_dependencies(){
		return apply_filters( 'gpte/admin/settings/default_integration_dependencies', $this->default_integration_dependencies );
	}

	/**
	 * Return the OpenAI token limit
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The token limit
	 */
	public function get_openai_token_limit(){
		return apply_filters( 'gpte/settings/get_openai_token_limit', $this->openai_token_limit );
	}

	/**
	 * Return the flows nonce data
	 *
	 * @return array - the flows nonce data
	 */
	public function get_nonce(){
		$nonce = array(
			'action' => GPTE_SLUG,
			'arg'    => GPTE_SLUG . '_nonce'
		);

		return $nonce;
	}

	/**
	 * Return the scripts and styles for the no-conflict mode
	 * 
	 * @since 1.0.0
	 *
	 * @return array - the scripts and styles
	 */
	public function get_no_conflict_scripts_styles( $type = 'all' ){

		$scripts_styles = apply_filters( 'gpte/admin/settings/no_conflict_scripts_styles', $this->no_conflict_scripts_styles, $type );

		switch( $type ){
			case 'scripts':
				$scripts_styles = $scripts_styles['scripts'];
				break;
			case 'styles':
				$scripts_styles = $scripts_styles['styles'];
				break;
		}

		return $scripts_styles;
	}

	/**
	 * Return the settings data
	 *
	 * @return array - the settings data
	 */
	public function get_settings(){

		return $this->default_settings;

	}

	public function save_settings( $new_settings, $update_all = true ){
		$success = false;

		if( empty( $new_settings ) ) {
			return $success;
		}

		$settings = $this->get_settings();

		// START General Settings
		foreach( $settings as $settings_name => $setting ){

			if( ! $update_all && ! isset( $new_settings[ $settings_name ] ) ){
				continue;
			}

			$value = '';

			if( $setting['type'] == 'checkbox' ){
				if( ! isset( $new_settings[ $settings_name ] ) || ! $new_settings[ $settings_name ] ){
					$value = 'no';
				} else {
					$value = 'yes';
				}
			} elseif( $setting['type'] == 'text' ){
				if( isset( $new_settings[ $settings_name ] ) ){
					$value = esc_html( $new_settings[ $settings_name ] );
				}
			} elseif( $setting['type'] == 'select' ){
				if( isset( $new_settings[ $settings_name ] ) ){
					$value = esc_html( $new_settings[ $settings_name ] );
				}
			}

			update_option( $settings_name, $value );
			$settings[ $settings_name ][ 'value' ] = $value;
		}
		// END General Settings

		$success = true;

		do_action( 'gpte/admin/settings_saved', $new_settings );

		return $success;
	 }
}
