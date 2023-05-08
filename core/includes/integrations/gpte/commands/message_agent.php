<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_message_agent' ) ) :
	/**
	 * Load the message_agent command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_message_agent {

		public function get_details() {

			$parameter = array(
				'agent_id' => array(
					'required'          => true,
					'label'             => __( 'The agent ID', 'gpte' ),
					'short_description' => __( '(String) The agent name.', 'gpte' ),
				),
				'message' => array(
					'required'          => true,
					'label'             => __( 'The message', 'gpte' ),
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
				'command'            => 'message_agent', // required
				'name'              => __( 'Message GPT Agent', 'gpte' ),
				'parameter'         => $parameter,
				'returns'           => $returns,
				'returns_code'      => $returns_code,
				'short_description' => __( 'Send a message to a GPT agent.', 'gpte' ),
				'description'       => array(),
				'integration'       => 'gpte',
				'execution_type'    => true,
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			$agent_id = intval( GPTE()->helpers->get_data( $data, 'agent_id' ) );
			$message = GPTE()->helpers->get_data( $data, 'message' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $agent_id ) ){
				$return_args['msg']     = __( 'You did not set the agent_id argument.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $message ) ){
				$return_args['msg']     = __( 'You did not set the message argument.', 'gpte' );
				return $return_args;
			}

			$args = array(
				'role' => 'user',
				'content' => $message,
			);

			$added = GPTE()->chats->process_message( $agent_id, $args );
	
			if( ! empty( $added ) ){

				$return_args['success'] = true;
				$return_args['msg'] = __( 'Successfully messaged the agent.', 'gpte' );
				$return_args['data'] = $added;
				
			} else {
				$return_args['msg'] = __( 'An error occured while messaging the agent.', 'gpte' );
			}

			return $return_args;

		}
	}
endif;
