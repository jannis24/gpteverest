<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_delete_agent' ) ) :
	/**
	 * Load the delete_agent command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_delete_agent {

		public function get_details() {

			$parameter = array(
				'agent_id' => array(
					'required'          => true,
					'label'             => __( 'int $agent_id', 'gpte' ),
				),
			);

			return array(
				'command'            => 'delete_agent', // required
				'name'              => __( 'Delete GPT Agent', 'gpte' ),
				'parameter'         => $parameter,
				'short_description' => __( 'Delete a GPT agent within WordPress.', 'gpte' ),
				'integration'       => 'gpte',
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			$agent_id = intval( GPTE()->helpers->get_data( $data, 'agent_id' ) );
			
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

					$deleted = wp_delete_post( $agent_id, true );

					if( $deleted ){
						$return_args['success'] = true;
						$return_args['msg'] = __( 'Successfully deleted the agent.', 'gpte' );
					} else {
						$return_args['msg'] = __( 'An error occured while deleting the agent.', 'gpte' );
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
