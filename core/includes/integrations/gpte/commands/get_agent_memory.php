<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_get_agent_memory' ) ) :
	/**
	 * Load the get_agent_memory command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_get_agent_memory {

		public function get_details() {

			$parameter = array(
				'agent_id' => array(
					'required'          => true,
					'label'             => __( 'int $agent_id', 'gpte' ),
					'short_description' => __( '(String) The agent name.', 'gpte' ),
				),
			);

			$returns           = array(
				'success' => array( 'short_description' => __( '(Bool) True if the command was successful, false if not. E.g. array( \'success\' => true )', 'gpte' ) ),
				'msg'     => array( 'short_description' => __( '(String) Further information about the command status.', 'gpte' ) ),
				'data'    => array( 'short_description' => __( '(Array) Further information about the request.', 'gpte' ) ),
			);

			$returns_code = array (
				'success' => true,
				'msg' => 'The query has been executed successfully.',
				'data' => 
				array (
				  'rows' => 
				  array (
					0 => 
					array (
					  'option_id' => '1',
					  'option_name' => 'siteurl',
					  'option_value' => 'https://yourdomain.test',
					  'autoload' => 'yes',
					),
					1 => 
					array (
					  'option_id' => '2',
					  'option_name' => 'home',
					  'option_value' => 'https://yourdomain.test',
					  'autoload' => 'yes',
					),
				  ),
				  'table_keys' => 
				  array (
					0 => 'option_id',
					1 => 'option_name',
					2 => 'option_value',
					3 => 'autoload',
				  ),
				),
			);

			return array(
				'command'            => 'get_agent_memory', // required
				'name'              => __( 'Get GPT Agent Memory', 'gpte' ),
				'parameter'         => $parameter,
				'returns'           => $returns,
				'returns_code'      => $returns_code,
				'short_description' => __( 'Get the memory of a GPT agent within WordPress.', 'gpte' ),
				'description'       => array(),
				'integration'       => 'gpte',
				'premium'           => true,
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			$agent_id = GPTE()->helpers->get_data( $data, 'agent_id' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $agent_id ) ){
				$return_args['msg']     = __( 'You did not set the agent_id argument.', 'gpte' );
				return $return_args;
			}

			$agent = get_post( $agent_id );
	
			if( ! empty( $agent ) && ! is_wp_error( $agent ) ){

				if( $agent->post_type !== 'gptechats' ){
					$return_args['msg'] = __( 'The agent_id is not a valid agent.', 'gpte' );
				} else {

					$chat_messages = GPTE()->chats->get_chat_messages( $agent_id );

					$memory = null;
					if( ! empty( $chat_messages ) ){
						$memory = GPTE()->prompts->build_memory( $chat_messages );
					}

					if( $memory !== null ){
						$return_args['success'] = true;
						$return_args['msg'] = __( 'Memory retrieved.', 'gpte' );
						$return_args['memory'] = $memory;
					} else {
						$return_args['msg'] = __( 'No memory available.', 'gpte' );
					}
					
				}
				
			} else {
				$return_args['msg'] = __( 'An error occured while finding the agent.', 'gpte' );

				if( is_wp_error( $agent ) ){
					$return_args['data']['error'] = $agent->get_error_message();
				}
				
			}

			return $return_args;

		}
	}
endif;
