<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_deploy_agent' ) ) :
	/**
	 * Load the deploy_agent command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_deploy_agent {

		public function get_details() {

			$parameter = array(
				'name' => array(
					'required'          => true,
					'label'             => __( 'Agent name', 'gpte' ),
				),
				'task' => array(
					'required'          => true,
					'label'             => __( 'Short task description', 'gpte' ),
				),
				'prompt' => array(
					'required'          => true,
					'label'             => __( 'The prompt', 'gpte' ),
				),
			);

			return array(
				'command'            => 'deploy_agent', // required
				'name'              => __( 'Deploy GPT Agent', 'gpte' ),
				'parameter'         => $parameter,
				'short_description' => __( 'Deploy a GPT agent within the current WordPress database.', 'gpte' ),
				'integration'       => 'gpte',
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = intval( GPTE()->helpers->get_data( $data, 'chat_id' ) );
			$name = GPTE()->helpers->get_data( $data, 'name' );
			$task = GPTE()->helpers->get_data( $data, 'task' );
			$prompt = GPTE()->helpers->get_data( $data, 'prompt' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $name ) ){
				$return_args['msg']     = __( 'You did not set the name argument.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $task ) ){
				$return_args['msg']     = __( 'You did not set the task argument.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $prompt ) ){
				$return_args['msg']     = __( 'You did not set the prompt argument.', 'gpte' );
				return $return_args;
			}

			$agent_args = array(
				'post_title' => sanitize_text_field( $name ),
				'post_excerpt' => sanitize_text_field( $task ),
				'post_parent' => $chat_id,
			);

			$agent_id = GPTE()->chats->create_chat( $agent_args );
	
			if( ! empty( $agent_id ) && ! is_wp_error( $agent_id ) ){

				if( ! empty( $prompt ) ){
					$args = array(
						'role' => 'user',
						'content' => $prompt,
					);
		
					$added = GPTE()->chats->process_message( $agent_id, $args );

					$return_args['msg'] = __( 'Agent successfully deployed with the given prompt.', 'gpte' );
				} else {
					$return_args['msg'] = __( 'Agent successfully deployed.', 'gpte' );
				}

				$return_args['success'] = true;
				$return_args['data']['agent_id'] = $agent_id;
			} else {
				$return_args['msg'] = __( 'An error occured while executing the GPTE.', 'gpte' );

				if( is_wp_error( $agent_id ) ){
					$return_args['data']['error'] = $agent_id->get_error_message();
				}
				
			}

			return $return_args;

		}
	}
endif;
