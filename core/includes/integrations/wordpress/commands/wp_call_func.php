<?php
if ( ! class_exists( 'GPTE_Integrations_wordpress_Commands_wp_call_func' ) ) :

	/**
	 * Load the wp_call_func action
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_wordpress_Commands_wp_call_func {

		public function get_details(){

			$parameter = array(
				'function_name' => array( 
					'required' => true, 
					'label' => __( 'callable $callback', 'gpte' ), 
					'short_description' => __( '(String) The function name of the PHP function you want to call.', 'gpte' ),
				),
				'function_definition' => array( //Necessary to make the AI understand parameter context
					//'label' => __( 'array $args', 'gpte' ),
					'label' => __( 'E.g. get_user_by( $field, $value )', 'gpte' ), 
					'short_description' => __( '(String) The arguments you want to send over within the function call. Use the variable name as the key and the value for the variable value. JSON and serialized data will be converted to its original format. To avoid it, please wrap the value in double quotes.', 'gpte' ),
				),
				'function_parameters' => array(
					//'label' => __( 'array $args', 'gpte' ),
					'label' => __( 'array $params', 'gpte' ), 
					'short_description' => __( '(String) The arguments you want to send over within the function call. Use the variable name as the key and the value for the variable value. JSON and serialized data will be converted to its original format. To avoid it, please wrap the value in double quotes.', 'gpte' ),
				),
			);

			ob_start();
?>
<p><?php echo __( 'This arugment allows you to pass custom variables to the function you are going to call. Below you see an example that explains in detail how a JSON looks like that used two vriables', 'gpte' ) ?></p>
<pre>
{
	'firstvar': 'Some string',
	'secondvar': {
		"your_key_1": "Some string", 
		"your_key_2": "Some string" 
	}
}
</pre>
<p><?php echo __( 'The above JSON will cause your function to receive two variables. Lets assume you have used <code>my_custom_function</code> as the value for the <code>function_name</code> argument. This will cause your function to be fired as followed:', 'gpte' ) ?></p>
<pre>
function my_custom_function( $firstvar, $secondvar ){

	//Do something

	return 'Demo Response';
} 
</pre>
<?php
			$parameter['function_parameters']['description'] = ob_get_clean();

			$returns = array(
				'success'		=> array( 'short_description' => __( '(Bool) True if the action was successful, false if not. E.g. array( \'success\' => true )', 'gpte' ) ),
				'data'		   => array( 'short_description' => __( '(mixed) The term id, as well as the taxonomy term id on success or wp_error on failure.', 'gpte' ) ),
				'msg'			=> array( 'short_description' => __( '(string) A message with more information about the current request. E.g. array( \'msg\' => "This action was successful." )', 'gpte' ) ),
			);

			$returns_code = array (
				'success' => true,
				'msg' => 'The function was successfully executed.',
				'data' => 
				array (
				  'response' => 'This is a demo response',
				  'error' => '',
				),
			);

			$description = array(
				'tipps' => array(
					__( 'If you add a JSON within the Value field, it will be automatically turned into an array. To avoid that, simply wrap your JSON within "double quotes". ', 'gpte' ),
					__( 'The response of this action contains the feedback from the custom PHP function you are trying to call.', 'gpte' ),
				),
			);

			return array(
				'command'			=> 'wp_call_func',
				'name'			  => __( 'Call WordPress function', 'gpte' ),
				'sentence'			  => __( 'call a WordPress function', 'gpte' ),
				'parameter'		 => $parameter,
				'returns'		   => $returns,
				'returns_code'	  => $returns_code,
				'short_description' => __( 'Call a WordPress function within WordPress.', 'gpte' ),
				'description'	   => $description,
				'integration'	   => 'wordpress',
				'premium' 			=> true,
			);

		}

		public function execute( $return_data, $data ){

			$return_args = array(
				'success' => false,
				'msg' => '',
				'data' => array(
					'response' => '',
					'error' => '',
				),
			);

			$function_name = GPTE()->helpers->get_data( $data, 'function_name' );
			$function_parameters = GPTE()->helpers->get_data( $data, 'function_parameters' );
			
			if( empty( $function_name ) ){
				$return_args['msg'] = __( "Please define the function_name argument.", 'action-wp_call_func' );
				return $return_args;
			}

			$validated_args = array();

			if( is_array( $function_parameters ) ){
				foreach( $function_parameters as $sak => $sav ){
					$validated_args[ $sak ] = GPTE()->helpers->maybe_format_string( $sav );
				}
			}

			$response = '';
			$error_message = '';

			if( function_exists( $function_name ) ){
				try {
					$response = call_user_func_array( $function_name, $validated_args );
				} catch ( \Exception $e ) {
					$error_message = $e->getMessage();
				}
			} else {
				$error_message = __( "The given function does not exist.", 'action-wp_call_func' );
			}
 
			if( $error_message === '' ) {
				$return_args = $response;
			} else {
				$return_args['data']['error'] = $error_message;
				$return_args['msg'] = __( "An error occured while executing the function callback", 'action-wp_call_func' );
			}

			return $return_args;
	
		}

	}

endif; // End if class_exists check.