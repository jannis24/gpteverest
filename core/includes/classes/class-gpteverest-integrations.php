<?php

/**
 * GPTE_Pro_Integrations Class
 *
 * This class contains all of the GPTEverest integrations
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The integrations class of the plugin.
 *
 * @since 1.0.0
 * @package GPTE
 * @author Jannis Thuemmig
 */
class GPTE_Pro_Integrations {

	/**
	 * All available integrations
	 *
	 * @since 1.0.0
	 * @var - All available integrations
	 */
	public $integrations = array();

    /**
	 * Execute feature related hooks and logic to get 
	 * everything running
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function execute(){

		add_action( 'plugins_loaded', array( $this, 'load_integrations' ), 10 );
		add_action( 'init', array( $this, 'safe_execute_command_callback' ), 2000 );

	}

    /**
	 * ######################
	 * ###
	 * #### INTEGRATION AUTOLOADER
	 * ###
	 * ######################
	 */

     /**
      * Initialize all default integrations
      *
      * @return void
      */
     public function load_integrations(){
         $integration_folder = $this->get_integrations_folder();
         $integration_folders = $this->get_integrations_directories();
  
         if( is_array( $integration_folders ) ){
             foreach( $integration_folders as $integration ){
                 $file_path = $integration_folder . DIRECTORY_SEPARATOR . $integration . DIRECTORY_SEPARATOR . $integration . '.php';
                 $this->register_integration( array(
                     'slug' => $integration,
                     'path' => $file_path,
                 ) );   
             }
         }
     }

     /**
      * Get an array contianing all of the currently given default integrations
      * The directory folder name acts as well as the integration slug.
      *
      * @return array The available default integrations
      */
    public function get_integrations_directories() {

        $integrations = array();
		
        try {
            $integrations = GPTE()->helpers->get_folders( $this->get_integrations_folder() );
        } catch ( Exception $e ) {
            throw GPTE()->helpers->log_issue( $e->getTraceAsString() );
        }

		return apply_filters( 'gpte/integrations/get_integrations_directories', $integrations );
	}

    /**
     * Get the GPTEverest content folder
     * If it does not exist, create it
     *
     * @return string The folder path
     */
    public function get_gpte_folder( $sub_path = '' ){

        $sub_path = sanitize_title( $sub_path );
        $folder_base = GPTE_PLUGIN_DIR . 'core' . DIRECTORY_SEPARATOR . 'includes';

        /**
         * Filter the folder base of GPTEverest
         * 
         * @since 1.0.0
         * @param string The folder path
         * @param string The sub path if given
         */
        $folder_base = apply_filters( 'gpte/integrations/get_gpte_folder/folder_base', $folder_base, $sub_path );

        if( $sub_path ){
            $folder_base .= DIRECTORY_SEPARATOR . $sub_path;
        }

        return apply_filters( 'gpte/integrations/get_gpte_folder', $folder_base, $sub_path );
    }

    /**
     * Get the integration folder
     * If it does not exist, create it
     *
     * @return string The folder path
     */
    public function get_integrations_folder( $integration = '' ){

        $integration = sanitize_title( $integration );
        $folder_base = $this->get_gpte_folder( 'integrations' );

        if( $integration ){
            $folder_base .= DIRECTORY_SEPARATOR . $integration;
        }

        return apply_filters( 'gpte/integrations/get_integrations_folder', $folder_base, $integration );
    }

    public function get_integrations_url( $integration = '' ){

        $integration = sanitize_title( $integration );
        $integrations_path = 'gpte' . DIRECTORY_SEPARATOR . 'integrations';

        if( $integration ){
            $integrations_path .= DIRECTORY_SEPARATOR . $integration;
        }

        $integrations_url = content_url( $integrations_path . DIRECTORY_SEPARATOR );    

        return apply_filters( 'gpte/integrations/get_integrations_url', $integrations_url );
    }

    /**
     * Register an integration 
     * 
     * This function can also be used to register third-party extensions. 
     * The following parameters are required: 
     * 
     * "path" => contains the integrations full path + file name + file extension
     * "slug" => contains the slug (folder name) of the integration
     * 
     * All other values are dynamically included (in case you define them.)
     *
     * @param array $integration
     * @return bool Whether the integration was added or not
     */
    public function register_integration( $integration = array() ){
        $return = false;
        $default_dependencies = GPTE()->settings->get_default_integration_dependencies();
        $wp_content_dir = GPTE()->helpers->get_wp_content_dir();

        if( is_array( $integration ) && isset( $integration['slug'] ) && isset( $integration['path'] ) ){
            $path = $integration['path'];
            $slug = $integration['slug'];
            $integration_basename = wp_basename( $path );

            if( file_exists( $path ) ){
                require_once $path;
                
                $directory = dirname( $path );
                $class = $this->get_integration_class( $slug );
                if( ! empty( $class ) && class_exists( $class ) && ! isset( $this->integrations[ $slug ] ) ){
                    $integration_class = new $class();
        
                    $is_active = ( ! method_exists( $integration_class, 'is_active' ) || method_exists( $integration_class, 'is_active' ) && $integration_class->is_active() ) ? true : false;
                    $is_active = apply_filters( 'gpte/integrations/integration/is_active', $is_active, $slug, $class, $integration_class );

                    if( $is_active ) {
                        $this->integrations[ $slug ] = $integration_class;

                        //Since v5.2, we pre-load the details within the integration for performance and to centralize
                        $integration_details = ( method_exists( $integration_class, 'get_details' ) ) ? $integration_class->get_details() : null;
                        if( $integration_details !== null ){
                            $this->integrations[ $slug ]->details = $integration_details;

                            if( is_array( $this->integrations[ $slug ]->details ) && isset( $this->integrations[ $slug ]->details['icon'] ) ){

                                //prevent custom integrations from auto-applying the path
                                $url_protocol = 'http';
                                if( substr( $this->integrations[ $slug ]->details['icon'], 0, strlen( $url_protocol ) ) !== $url_protocol ){
                                    
                                    //In some environments this is necessary to adjust the path to the local separator
                                    $wp_content_dir_path_validated = str_replace( '/', DIRECTORY_SEPARATOR, $wp_content_dir );
                                    
                                    $icon_url = str_replace( $wp_content_dir_path_validated, '', $path );
                                    $icon_url = str_replace( $integration_basename, ltrim( $this->integrations[ $slug ]->details['icon'], '/' ), $icon_url );
    
                                    $this->integrations[ $slug ]->details['icon'] = content_url( $icon_url );
                                }
                                
                            }
                        }
        
                        //Register Depenencies
                        foreach( $default_dependencies as $default_dependency ){

                            //Make sure the default dependencies exists
                            if( ! property_exists( $this->integrations[ $slug ], $default_dependency ) ){
                                $this->integrations[ $slug ]->{$default_dependency} = new stdClass();
                            }

                            if( ! is_array( $this->integrations[ $slug ]->{$default_dependency} ) ){
                                $this->integrations[ $slug ]->{$default_dependency} = new stdClass();
                            }

                            $dependency_path = $directory . DIRECTORY_SEPARATOR . $default_dependency;
                            if( is_dir( $dependency_path ) ){
                                $dependencies = array();

                                try {
                                    $dependencies = GPTE()->helpers->get_files( $dependency_path, array(
                                        'index.php'
                                    ) );
                                } catch ( Exception $e ) {
                                    throw GPTE()->helpers->log_issue( $e->getTraceAsString() );
                                }
    
                                if( is_array( $dependencies ) && ! empty( $dependencies ) ){

                                    foreach( $dependencies as $dependency ){
                                        $basename = basename( $dependency );
                                        $basename_clean = basename( $dependency, ".php" );
    
                                        $ext = pathinfo( $basename, PATHINFO_EXTENSION );
                                        if ( (string) $ext !== 'php' ) {
                                            continue;
                                        }
    
                                        require_once $dependency_path . DIRECTORY_SEPARATOR . $dependency;
    
                                        $dependency_class = $this->get_integration_class( $slug, $default_dependency, $basename_clean );

                                        if( class_exists( $dependency_class ) ){
                                            $dependency_class_object = new $dependency_class();
    
                                            $is_active = ( ! method_exists( $dependency_class_object, 'is_active' ) || method_exists( $dependency_class_object, 'is_active' ) && $dependency_class_object->is_active() ) ? true : false;
                                            $is_active = apply_filters( 'gpte/integrations/dependency/is_active', $is_active, $slug, $basename_clean, $dependency_class, $dependency_class_object );

                                            if( $is_active ){

                                                $details = ( method_exists( $dependency_class_object, 'get_details' ) ) ? $dependency_class_object->get_details() : null;
                                                if( $details !== null && is_array( $details ) ){
                                                    $dependency_class_object->details = $details;
                                                }

                                                $this->integrations[ $slug ]->{$default_dependency}->{$basename_clean} = $dependency_class_object;
                                            }
    
                                        }
                                    }
                                }
                            }
                        }
        
                    }
        
                    $return = true;
                }
    
            }
        }

        return $return;
    }

    /**
     * Builds the dynamic class based on the integration name and a sub file name
     *
     * @param string $integration The integration slug
     * @param string $type The type fetched from GPTE()->settings->get_default_integration_dependencies()
     * @param string $sub_class A sub file name in case we add something from te default dependencies
     * @return string The integration class
     */
    public function get_integration_class( $integration, $type = '', $sub_class = '' ){
        $class = false;

        if( ! empty( $integration ) ){
            $class = 'GPTE_Integrations_' . $this->validate_class_name( $integration );
        }

        if( ! empty( $type ) && ! empty( $sub_class ) ){
            $validate_class_type = ucfirst( strtolower( $type ) );
            $class .= '_' . $validate_class_type . '_' . $this->validate_class_name( $sub_class );
        }
        
        return apply_filters( 'gpte/integrations/get_integration_class', $class );
    }

    /**
     * Format the class name to make it compatible with our
     * dynamic structure
     *
     * @param string $class_name
     * @return string The class name
     */
    public function validate_class_name( $class_name ){

        $class_name = str_replace( ' ', '_', $class_name );
        $class_name = str_replace( '-', '_', $class_name );

        return apply_filters( 'gpte/integrations/validate_class_name', $class_name );
    }

    /**
     * Grab the details of a given integration
     *
     * @param string $slug
     * @return array The integration details
     */
    public function get_details( $slug ){
        $return = array();

        if( ! empty( $slug ) ){
            if( isset( $this->integrations[ $slug ] ) ){
                if( isset( $this->integrations[ $slug ]->details ) ){
                    $return = $this->integrations[ $slug ]->details;
                }
            }
        }

        return apply_filters( 'gpte/integrations/get_details', $return );
    }

    /**
     * Get all available integrations
     *
     * @param string $slug
     * @return array The integration details
     */
    public function get_integrations( $slug = false ){
        $return = $this->integrations;

        if( $slug !== false ){
            if( isset( $this->integrations[ $slug ] ) ){
                $return = $this->integrations[ $slug ];
            } else {
                $return = false;
            }
        }

        return apply_filters( 'gpte/integrations/get_integrations', $return );
    }

    /**
     * Grab a specific helper from the given integration
     *
     * @param string $integration The integration slug (folder name)
     * @param string $helper The helper slug (file name)
     * @return object|stdClass The helper class
     */
    public function get_helper( $integration, $helper ){
        $return = new stdClass();

        if( ! empty( $integration ) && ! empty( $helper ) ){
            if( isset( $this->integrations[ $integration ] ) ){
                if( property_exists( $this->integrations[ $integration ], 'helpers' ) ){
                    if( property_exists( $this->integrations[ $integration ]->helpers, $helper ) ){
                        $return = $this->integrations[ $integration ]->helpers->{$helper};
                    }
                }
            }
        }

        return apply_filters( 'gpte/integrations/get_helper', $return );
    }

    /**
     * Get a list of all available commands
     * 
     * @since 1.0.0
     *
     * @param mixed $integration_slug - The slug of a single integration
     * @param mixed $integration_command - The slug of a single command
     * 
     * @return array A list of commands or a single command
     */
    public function get_commands( $integration_slug = false, $integration_command = false ){

        $commands = array();

        if( ! empty( $this->integrations ) ){
            foreach( $this->integrations as $si ){
                if( property_exists( $si, 'commands' ) ){
                    foreach( $si->commands as $command_slug => $command ){

                        if( isset( $this->commands[ $command_slug ] ) ){
                            $commands[ $command_slug ] = $this->commands[ $command_slug ];
                        } else {
                            if( isset( $command->details ) ){
                                $details = $command->details;
                                
                                if( is_array( $details ) && isset( $details['command'] ) && ! empty( $details['command'] ) ){
        
                                    //Validate parameter globally
                                    if( isset( $details['parameter'] ) && is_array( $details['parameter'] ) ){

                                        foreach( $details['parameter'] as $arg => $arg_data ){
        
                                            //Add name
                                            if( ! isset( $details['parameter'][ $arg ]['id'] ) ){
                                                $details['parameter'][ $arg ]['id'] = $arg;
                                            }
        
                                            //Add label
                                            if( ! isset( $details['parameter'][ $arg ]['label'] ) ){
                                                $details['parameter'][ $arg ]['label'] = $arg;
                                            }
        
                                            //Add type
                                            if( ! isset( $details['parameter'][ $arg ]['type'] ) ){
                                                $details['parameter'][ $arg ]['type'] = 'text';
                                            }
        
                                            //Add required
                                            if( ! isset( $details['parameter'][ $arg ]['required'] ) ){
                                                $details['parameter'][ $arg ]['required'] = false;
                                            }
        
                                            //Add variable
                                            if( ! isset( $details['parameter'][ $arg ]['variable'] ) ){
                                                $details['parameter'][ $arg ]['variable'] = true;
                                            }
        
                                            //Verify choices to the new structure
                                            if( isset( $details['parameter'][ $arg ]['choices'] ) ){
                                                foreach( $details['parameter'][ $arg ]['choices'] as $single_choice_key => $single_choice_data ){

                                                    //Make sure we always serve the same values
                                                    if( is_array( $single_choice_data ) ){

                                                        if( ! isset( $single_choice_data['value'] ) ){
                                                            $details['parameter'][ $arg ]['choices'][ $single_choice_key ]['value'] = $single_choice_key;
                                                        }

                                                        if( ! isset( $single_choice_data['label'] ) ){
                                                            $details['parameter'][ $arg ]['choices'][ $single_choice_key ]['label'] = $single_choice_key;
                                                        }

                                                    } elseif( is_string( $single_choice_data ) ){
                                                        $details['parameter'][ $arg ]['choices'][ $single_choice_key ] = array(
                                                            'label' => $single_choice_data,
                                                            'value' => $single_choice_key,
                                                        );
                                                    }

                                                }
                                            }
                                            
                                        }
                                    }

                                    $commands[ $details['command'] ] = $details;
                                    $this->commands[ $command_slug ] = $details;
                                }
                            }
                        }
                        
                    }
                }
            }
        }
 
        $commands = apply_filters( 'gpte/integrations/get_commands', $commands, $integration_slug, $integration_command );
   
        $commands_output = $commands;

        if( $integration_slug !== false ){
            $commands_output = array();

            foreach( $commands as $command_slug => $command_data ){

                //Continue only if the integration matches
                if( 
                    ! is_array( $command_data ) 
                    || ! isset( $command_data['integration'] ) 
                    || $command_data['integration'] !== $integration_slug 
                ){
                    continue;
                }

                $commands_output[ $command_slug ] = $command_data;

            }
        }
        
        if( $integration_command !== false ){
            if( isset( $commands_output[ $integration_command ] ) ){
                $commands_output = $commands_output[ $integration_command ];
            }
        }

        return apply_filters( 'gpte/integrations/get_commands/output', $commands_output, $commands, $integration_slug, $integration_command );
    }

    public function safe_execute_command( $default_return_data, $command, $data = array() ){
        $return_data = $default_return_data;
        
        $home_url = home_url();
        $api_key = get_option( 'gpte_openai_api_key' );
        $secret_key = sanitize_title( $api_key . $home_url );
        $secret_data = array(
            'url' => $home_url,
            'key' => $api_key,
        );
        $secret = GPTE()->helpers->generate_signature( $secret_data, $secret_key );

        $request_data = array(
            'command' => $command,
            'data' => $data,
        );

        $body = array(
            'gpteSecret' => GPTE()->helpers->generate_signature( $request_data, $secret ),
            'gptedata' => $request_data,
        );

        $post_args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type: application/json'
            ),
            'body' => json_encode( $body ),
            'blocking' => true,
            'timeout' => 60,
        );

        if( GPTE()->helpers->is_dev() ){
            $post_args['sslverify'] = false;
            $post_args['reject_unsafe_urls'] = false;
        }

        $action_url = $home_url . '?gpteactioncall=' . urlencode( $secret );
        $response = wp_remote_post( $action_url, $post_args );
        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );  

        if( $response_code === 200 ){
            if( GPTE()->helpers->is_json( $body ) ){
                $return_data = json_decode( $body, true );
            } else {
                $return_data = $body;
            }
        } else {
            $return_data['success'] = false;
            $return_data['msg'] = sprintf( __( 'An error with the following HTTP error code occured: %d' ), $response_code );
            $return_data['data'] = GPTE()->helpers->generate_wp_error_response( $response );
        }
        

        return apply_filters( 'gpte/integrations/safe_execute_command', $return_data, $command, $data, $default_return_data );
    }

    /**
     * Execute the acion logic but in a separate instance to make sure nothing breaks.
     *
     * @return void
     */
    public function safe_execute_command_callback(){
        
        $home_url = home_url();
        $api_key = get_option( 'gpte_openai_api_key' );
        $secret_key = sanitize_title( $api_key . $home_url );
        $data = array(
            'url' => $home_url,
            'key' => $api_key,
        );
        $secret = GPTE()->helpers->generate_signature( $data, $secret_key );
        $response = array(
            'success' => false,
            'msg' => '',
        );

        if( 
            isset( $_GET['gpteactioncall'] ) 
            && $_GET['gpteactioncall'] === $secret
        ){

            $request_data = file_get_contents('php://input');

            if( ! empty( $request_data ) && GPTE()->helpers->is_json( $request_data ) ){
                $request_data = json_decode( $request_data, true );

                if( 
                    isset( $request_data['gpteSecret'] )
                    && isset( $request_data['gptedata'] )
                ){  
    
                    $secret_array = $request_data['gptedata'];
                    $command_secret = GPTE()->helpers->generate_signature( $secret_array, $secret );
    
                    if( $command_secret === $request_data['gpteSecret'] ){
                        $response = $this->execute_command( array(), $request_data['gptedata']['command'], $request_data['gptedata']['data'] );
    
                        header( 'Content-Type: application/json' );
                        echo json_encode( $response );
                        die();
                    } else {
                        $response['msg'] = __( 'Invalid data.' );
    
                        header( 'Content-Type: application/json' );
                        echo json_encode( $response );
                        die();
                    }
                } else {
    
                    $response['msg'] = __( 'Invalid auth data.' );
    
                    header( 'Content-Type: application/json' );
                    echo json_encode( $response );
                    die();
                }
            } else {
                $response['msg'] = __( 'Invalid request data.' );

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();
            }
            
        }
        
    }

    /**
     * Execute the acion logic
     *
     * @param array $default_return_data
     * @param string $command
     * @return array The data we return to the command caller
     */
    public function execute_command( $default_return_data, $command, $data = array() ){
        $return_data = $default_return_data;

        if( empty( $data ) || ! is_array( $data ) ){
            $data = array();
        }

        if( ! empty( $this->integrations ) ){
            foreach( $this->integrations as $si ){
                if( property_exists( $si, 'commands' ) ){
                    $commands = $si->commands;
                    if( is_object( $commands ) && isset( $commands->{$command} ) ){
                        if( method_exists( $commands->{$command}, 'execute' ) ){
                            $return_data = $commands->{$command}->execute( $return_data, $data );
                        }
                    }
                }
            }
        }

        return apply_filters( 'gpte/integrations/execute_command', $return_data, $command, $data, $default_return_data );
    }

}
