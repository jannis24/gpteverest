<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_add_to_memory' ) ) :
	/**
	 * Load the add_to_memory command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_add_to_memory {

		public function get_details() {

			$parameter = array(
				'memory' => array(
					'required'          => true,
					'label'             => __( 'The memory to add', 'gpte' ),
				),
			);

			return array(
				'command'            => 'add_to_memory', // required
				'name'              => __( 'Add to memory', 'gpte' ),
				'parameter'         => $parameter,
				'short_description' => __( 'Add data to the memory of an agent.', 'gpte' ),
				'integration'       => 'gpte',
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			$memory = GPTE()->helpers->get_data( $data, 'memory' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $memory ) ){
				$return_args['msg']     = __( 'You did not set the memory argument.', 'gpte' );
				return $return_args;
			}

			$agent = get_post( $chat_id );
	
			if( ! empty( $agent ) && ! is_wp_error( $agent ) ){

				if( $agent->post_type === 'gptechats' ){
					$return_args['success'] = true;
					$return_args['msg'] = __( 'Memory was added.', 'gpte' );
					$return_args['data'] = $memory;
				} else {

					$return_args['msg'] = __( 'No memory added.', 'gpte' );
					
				}
				
			} else {
				$return_args['msg'] = __( 'An error occured while adding the memory.', 'gpte' );
			}

			return $return_args;

		}
	}
endif;
