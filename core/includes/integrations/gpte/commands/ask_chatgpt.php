<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_ask_chatgpt' ) ) :
	/**
	 * Load the ask_chatgpt command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_ask_chatgpt {

		public function get_details() {

			$parameter = array(
				'prompt' => array(
					'required'          => true,
					'label'             => __( 'The prompt', 'gpte' ),
				),
			);

			return array(
				'command'            => 'ask_chatgpt', // required
				'name'              => __( 'Ask ChatGPT', 'gpte' ),
				'parameter'         => $parameter,
				'short_description' => __( 'Ask ChatGPT.', 'gpte' ),
				'integration'       => 'gpte',
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			$prompt = GPTE()->helpers->get_data( $data, 'prompt' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}
			
			if( empty( $prompt ) ){
				$return_args['msg']     = __( 'You did not set the prompt argument.', 'gpte' );
				return $return_args;
			}

			$openai_args = array(
				'messages' => array(
					array(
						'role' => 'user',
						'content' => $prompt,
					)
				),
				'temperature' => 0,
			);
	
			$answer = GPTE()->openai->request_chat_completion( $openai_args );
	
			if(
				is_array( $answer )
				&& isset( $answer['success'] )
			){
	
				if( 
					$answer['success']
					&& isset( $answer['data'] )
					&& isset( $answer['data']['role'] )
					&& isset( $answer['data']['content'] )
				){
					$return_args['success'] = true;
					$return_args['msg'] = __( 'ChatGPT answered.', 'gpte' );
					$return_args['data'] = array( 'answer' => $answer['data']['content'] );
				} else {
					$return_args['msg'] = __( 'The answer was not successful.', 'gpte' );
				}

			} else {
				$return_args['msg'] = __( 'An error occured while retrieving the answer.', 'gpte' );
			}

			return $return_args;

		}
	}
endif;
