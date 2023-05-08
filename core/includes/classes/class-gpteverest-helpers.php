<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Gpteverest_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_Helpers
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Helpers{

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	 /**
     * Check if a given string is a json
     *
	 * @param $string - the string that should be checked
	 *
	 * @return bool - True if it is json, otherwise false
	 */
	public function is_json( $string ) {

		if( ! is_string( $string ) ){
			return false;
		}

		json_decode( $string );
		if( json_last_error() == JSON_ERROR_NONE ){
			return true;
		}

		json_decode( $string, true );
		if( json_last_error() == JSON_ERROR_NONE ){
			return true;
		}

		return false;
	}

	public function extract_json_string( $input_string ) {
		preg_match_all( '/\{(?:[^{}]|(?R))*\}/x', $input_string, $matches );
    
		if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {
			return $matches[0];
		}

		return $input_string;
	}

	/**
	 * Get all folders within a given path
	 *
	 * @since 1.0.0
	 * @return array The folders
	 */
	public function get_folders( $path ){

		$folders = array();

		if( ! empty( $path ) && is_dir( $path ) ){
			$all_folders = scandir( $path );
			foreach( $all_folders as $single ){
				$full_path = $path . DIRECTORY_SEPARATOR . $single;

				if( $single == '..' || $single == '.' || ! is_dir( $full_path ) ){
					continue;
				}

				$folders[] = $single;

			}
		}


		return apply_filters( 'gpte/helpers/get_folders', $folders );
	}

	/**
     * Log certain data within the debug.log file
	 */
	public function log_issue( $text ){

		error_log( $text );

	}

	/**
	 * Get all files within a given path
	 *
	 * @since 1.0.0
	 * @return array The files
	 */
	public function get_files( $path, $ignore = array() ){

		$files = array();
		$default_ignore = array(
			'..',
			'.'
		);

		$ignore = array_merge( $default_ignore, $ignore );

		if( ! empty( $path ) && is_dir( $path ) ){
			$all_files = scandir( $path );
			foreach( $all_files as $single ){
				$full_path = $path . DIRECTORY_SEPARATOR . $single;

				if( in_array( $single, $ignore ) || ! file_exists( $full_path ) ){
					continue;
				}

				$files[] = $single;

			}
		}


		return apply_filters( 'gpte/helpers/get_files', $files );
	}

	public function get_data( $data, $key ){
		$return = null;

		if( is_array( $data ) && isset( $data[ $key ] ) ){
			$return = $data[ $key ];
		}

		return apply_filters( 'gpte/helpers/get_data', $return, $data, $key );
	}

	/**
	 * Builds an url out of the mai values
	 *
	 * @param $with_args - with query parameter or not
	 * @param $relative - a validated version of the URL
	 * @return string - the url
	 */
	public function get_current_url( $with_args = true, $relative = false ){	
		if( ! $relative ){
			$current_url = ( isset( $_SERVER['HTTPS'] ) && in_array( $_SERVER['HTTPS'], array( 'on', 'On', 'ON', '1', true ) ) ) ? 'https://' : 'http://';

			$host_part = $_SERVER['HTTP_HOST'];
	
			//Support custom ports (since 4.2.0)
			$host_part = str_replace( ':80', '', $host_part );
			$host_part = str_replace( ':443', '', $host_part );
	
			$current_url .= sanitize_text_field( $host_part ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
		} else {
			$current_url = sanitize_text_field( $_SERVER['REQUEST_URI'] );
		}

	    if( ! $with_args ){
	        $current_url = strtok( $current_url, '?' );
        }

		return apply_filters( 'gpte/helpers/get_current_url', $current_url, $with_args, $relative );
	}

	/**
	 * Builds an url out of the given values
	 *
	 * @param $url - the default url to set the params to
	 * @param $args - the available args
	 * @return string - the url
	 */
	public function built_url( $url, $args ){
		if( ! empty( $args ) ){
			$url .= '?' . http_build_query( $args );
		}

		return $url;
	}

	/**
	 * Checks if the parsed param is available on a given site
	 *
	 * @return bool
	 */
	public function is_page( $param = null ){

		if( isset( $_GET['page'] ) ){
			if( ! empty( $param ) ){
				if( $_GET['page'] == $param ){
					return true;
				} else {
					return false;
				}
			} else {
				return true; //set it to true if no parameter is given but it is a page
			}
		}

		return false;
	}

	/**
	 * Check whether a developer version is used or not
	 * It is not recommended using this function
	 * anywhere.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public function is_dev(){
		$is_dev = false;

		if( defined( 'GPTE_DEV' ) ){
			if( GPTE_DEV ){
				$is_dev = true;
			}
		}

		return $is_dev;
	}

	/**
	 * Get the WordPress content directory
	 *
	 * @since 1.0.0
	 * @return string The content dir
	 */
	public function get_wp_content_dir(){
		$wp_content_dir = ( defined( 'WP_CONTENT_DIR' ) ) ? WP_CONTENT_DIR : ABSPATH . DIRECTORY_SEPARATOR . 'wp-content';

		return apply_filters( 'gpte/helpers/get_wp_content_dir', $wp_content_dir );
	}

	/**
     * This function validates all necessary tags for displayable content.
     *
	 * @param $content - The validated content
	 * @since 1.0.0
	 * @return mixed
	 */
	public function validate_local_tags( $content ){

	    $user = get_user_by( 'id', get_current_user_id() );

	    $user_name = 'there';
	    if( ! empty( $user ) && ! empty( $user->data ) && ! empty( $user->data->display_name ) ){
	        $user_name = $user->data->display_name;
        }

		$content = str_replace(
			array( '%home_url%', '%admin_url%', '%product_version%', '%product_name%', '%user_name%' ),
			array( home_url(), get_admin_url(), GPTE_VERSION, GPTE_NAME, $user_name ),
			$content
		);

		return $content;
    }

	/**
	 * Return a formatted date
	 *
	 * @since 1.0.0
	 * @param mixed $data
	 * @return array
	 */
	public function get_formatted_date( $date, $date_format = 'Y-m-d H:i:s' ) {

		$return = false;

		if( empty( $date ) ){
			return $return;
		}

		if( is_numeric( $date ) ){
			$return = date( $date_format, $date );
		} else {
			$return = date( $date_format, strtotime( $date ) );
		}

		return apply_filters( 'gpte/helpers/get_formatted_date', $return, $date, $date_format );
	}

	/**
	  * Create link HTML
	  *
	  * @since 1.0.0
	  *
	  * @param string $url
	  * @param string $text
	  * @param array $args
	  * @return string
	  */
	  public function create_link( $url, $text = '', $args = array() ){
		$defaults = array(
			'id' => '',
			'class' => '',
		);
	
		$args = wp_parse_args( $args, $defaults );
		$attributes = $this->format_html_attributes( $args );
	
		$link = sprintf( '<a href="%1$s"%3$s>%2$s</a>',
			esc_url( $url ),
			$text,
			$attributes ? ( ' ' . $attributes ) : ''
		);
		
		return apply_filters( 'gpte/helpers/create_link', $link );
	}

	/**
	  * Format HTML attributes
	  *
	  * @since 1.0.0
	  *
	  * @param array $atts
	  * @return string
	  */
	public function format_html_attributes( $atts ) {
		$output = '';
	
		foreach( $atts as $att => $value ){
			$att = strtolower( trim( $att ) );
	
			if( ! preg_match( '/^[a-z_:][a-z_:.0-9-]*$/', $att ) ){
				continue;
			}
	
			$value = trim( $value );
	
			if( $value !== '' ){
				$output .= sprintf( ' %s="%s"', $att, esc_attr( $value ) );
			}
		}
	
		$output = trim( $output );
	
		return apply_filters( 'gpte/helpers/format_html_attributes', $output );
	}

	/**
	 * Verify the current user and make allow the 
	 * permission to be customized
	 *
	 * @param string $capability
	 * @param string $permission_type
	 * @return bool
	 */
	public function current_user_can( $capability = '', $permission_type = 'default' ){
		return apply_filters( 'gpte/helpers/current_user_can', current_user_can( $capability ), $capability, $permission_type );
	}

	/**
	 * Corrects or re-formats a value based on a given format
	 *
	 * @since 1.0.0
	 * @param mixed $data
	 * @return mixed
	 */
	public function maybe_format_string( $string ) {

		$return = $string;

		if( ! empty( $return ) ){

			if( $this->is_json( $return ) ){
				$return = json_decode( $return, true );
			} else {
				$return = maybe_unserialize( $return );
			}

			//verify JSON and serialized information within 
			if( is_string( $return ) ){
				$trimmed_value = trim( $return, '"' );
				
				if( ! empty( $trimmed_value ) ){
					if( $this->is_json( $trimmed_value ) ){
						$return = $trimmed_value;
					} elseif( is_serialized( $trimmed_value ) ){
						$return = $trimmed_value;
					}
				}
			}

		}

		return apply_filters( 'gpte/helpers/maybe_format_string', $return, $string );
	}

	/**
	 * Validate the functions arguments of a given function
	 *
	 * @param mixed $function_name
	 * @param array $args
	 * @return array
	 */
	public function validate_function_args( $function_name, $args ) {
		$existing_args = array();

		if( function_exists( 'function_name' ) ){
			try {
				$reflection_function = new ReflectionFunction( $function_name );
				$function_parameters = $reflection_function->getParameters();
	
				foreach ( $function_parameters as $param ) {
					$param_name = $param->getName();
					if ( array_key_exists( $param_name, $args ) ) {
						$existing_args[ $param_name ] = $args[ $param_name ];
					}
				}
	
			} catch ( ReflectionException $e ) {
				//do nothing for now
			}
		}

		return apply_filters( 'gpte/helpers/validate_function_args', $existing_args, $function_name, $args );
	}

	/**
	 * Create signature from a given string
	 *
	 * @since 1.0.0
	 * @param mixed $data
	 * @return string
	 */
	public function generate_signature( $data, $secret ) {

		if( is_array( $data ) || is_string( $data ) ){
			$data = json_encode( $data );
		}

		$data = base64_encode( $data );
		$hash_signature = apply_filters( 'gpte/helpers/generate_signature', 'sha256', $data );

		return base64_encode( hash_hmac( $hash_signature, $data, $secret, true ) );
	}

	/**
	 * Get the nonce field
	 *
	 * @param array $nonce_data
	 * @return string
	 */
	public function get_nonce_field( $nonce_data ){

		if( ! is_array( $nonce_data ) || ! isset( $nonce_data['action'] ) || ! isset( $nonce_data['arg'] ) ){
			return '';
		}

		ob_start();
		wp_nonce_field( $nonce_data['action'], $nonce_data['arg'] );
		$nonce = ob_get_clean();

		$nonce = str_replace( 'id="', 'id="' . mt_rand( 1, 999999 ) . '-', $nonce );

		return apply_filters( 'gpte/helpers/get_nonce_field', $nonce, $nonce_data );
	}

	/**
     * Generate a formatted response
     * based on a given WP_Error object
     *
     * @param WP_Error $wp_error
     * @return array The formatted data 
     */
    public function generate_wp_error_response( $wp_error ){

        $response_data = array(
			'msg' => '',
			'error_code' => '',
		);

        if( ! empty( $wp_error ) && is_wp_error( $wp_error ) ){
            $response_data['msg'] = $wp_error->get_error_message();
			$response_data['error_code'] = $wp_error->get_error_code();
        }

        return apply_filters( 'gpte/http/generate_wp_error_response', $response_data, $wp_error );
    }

}
