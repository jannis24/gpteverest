<?php

/**
 * Gpteverest_Prompts Class
 *
 * This class manages everything around prompts and their setup.
 *
 * @since 1.0.0
 */

/**
 * The Prompts class
 *
 * @package		GPTE
 * @subpackage	Classes/GPTEverest_Prompts
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Prompts {

	public function send_prompt( $chat_id, $attr = array() ){

		$prompt = array(
			'success' => false,
			'msg' => '',
			'data' => array(
				'role' => '',
				'content' => '',
			),
		);
		
		$chat_data = $this->prepare_prompt_for_openai( $chat_id );

		$openai_args = array(
			'messages' => $chat_data,
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

				//Streamline data
				if( ! GPTE()->helpers->is_json( $answer['data']['content'] ) ){
					$found_json = GPTE()->helpers->extract_json_string( $answer['data']['content'] );
					if(
						is_array( $found_json ) 
						&& isset( $found_json[0] )
					){

						if( GPTE()->helpers->is_json( $found_json[0] ) ){
							$json_answer = json_decode( $found_json[0], true );
						} else {
							
							//If not a valid JSON, ask ChatGPT to correct it
							$json_answer = $this->maybe_correct_json( $found_json );

						}
						
					} else {

						//If not a valid JSON, ask ChatGPT to correct it
						$json_answer = $this->maybe_correct_json( $found_json );

						//Fallback
						if( ! is_array( $json_answer ) ){
							$json_answer = $answer['data']['content'];
						}
						
					}
				} else {
					$json_answer = json_decode( $answer['data']['content'], true );
				}

				//todo - verify JSON answer here and apply defualt preset instead

				if( ! empty( $json_answer ) ){

					if( is_array( $json_answer ) && isset( $json_answer['thoughts'] ) ){
			
						if( is_array( $json_answer ) && $json_answer['thoughts'] ){
			
							if( $answer['data']['role'] ){
								$json_answer['role'] = $answer['data']['role'];
							} else {
								$json_answer['role'] = 'assistant';
							}
							
							//Streamline default data
							if( isset( $json_answer['thoughts']['speak'] ) ){	
								$json_answer['content'] = $json_answer['thoughts']['speak'];
							} else {
								$json_answer['content'] = __( 'An error occured while fetching the content.', 'gpteverest' );
							}
							
							//Streamline default data
							if( isset( $json_answer['thoughts']['plan'] ) ){
								$plan_array = array();
								
								if( is_array( $json_answer['thoughts']['plan'] ) ){
									$plan_array = $json_answer['thoughts']['plan'];
								} else {

									if( GPTE()->helpers->is_json( $json_answer['thoughts']['plan'] ) ){
										$plan_array = json_decode( $json_answer['thoughts']['plan'], true );
									} else {
										$plan_array = explode( '- ', $json_answer['thoughts']['plan'] );
									}
									
								}

								$json_answer['thoughts']['plan'] = $plan_array;
							} else {
								$json_answer['thoughts']['plan'] = array();
							}
			
							$prompt['success'] = true;
							$prompt['msg'] = __( 'The prompt response was successfully retrieved.', 'gpteverest' );
							$prompt['data'] = $json_answer;
						} else {
							$prompt['success'] = true;
							$prompt['msg'] = __( 'An incomplete answer was given.', 'gpteverest' );
							$prompt['data'] = array(
								'role' => $answer['data']['role'],
								'content' => json_encode( $json_answer ),
							);
						}
					} else {
						$prompt['success'] = true;
						$prompt['msg'] = __( 'An invalid answer was given.', 'gpteverest' );
						$prompt['data'] = array(
							'role' => $answer['data']['role'],
							'content' => is_string( $json_answer ) ? $json_answer : $answer['data']['content'],
						);
					}

				} else {
					$prompt['msg'] = __( 'An invalid answer was given.', 'gpteverest' );
					$prompt['data'] = array(
						'role' => $answer['data']['role'],
						'content' => is_string( $json_answer ) ? $json_answer : $answer['data']['content'],
					);
				}

			} else {
				$prompt['msg'] = __( 'Sorry, but I could not retrieve any data.', 'gpteverest' );
				$prompt['data'] = array(
					'role' => 'gptecore',
					'content' => __( 'Oops, my thought processs was not complete.', 'gpteverest' ),
				);
			}

		} else {
			$prompt['msg'] = __( 'Sorry, but no successful data was given.', 'gpteverest' );
			$prompt['data'] = array(
				'role' => 'gptecore',
				'content' => sprintf( __( 'Oops, something went wrong: %s', 'gpteverest' ), $answer['msg'] ),
			);
		}

		return $prompt;
	}

	public function maybe_correct_json( $found_json ){
		$return_json = $found_json;

		if( is_array( $found_json ) ){
			$found_json = json_encode( $found_json );
		}

		$json_prompt = $this->get_json_prompt( array( 'json' => $found_json ) );

		$openai_args = array(
			'messages' => array(
				array(
					'role' => 'user',
					'content' => $json_prompt,
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
				if( ! GPTE()->helpers->is_json( $answer['data']['content'] ) ){
					$possible_json = GPTE()->helpers->extract_json_string( $answer['data']['content'] );
					if(
						is_array( $possible_json ) 
						&& isset( $possible_json[0] )
					){

						if( GPTE()->helpers->is_json( $possible_json[0] ) ){
							$return_json = json_decode( $possible_json[0], true );
						}
						
					}
				} else {

					if( is_array( $answer['data']['content'] ) ){
						$return_json = $answer['data']['content'];
					} elseif( is_object( $answer['data']['content'] ) ) {
						$return_json = json_decode( json_encode( $answer['data']['content'] ), true );
					} else {
						$return_json = json_decode( $answer['data']['content'], true );
					}
					
				}
			}
		}

		return apply_filters( 'gpte/prompts/maybe_correct_json', $return_json, $found_json );
	}

	/**
	 * ######################
	 * ###
	 * #### OPENAI PREPS
	 * ###
	 * ######################
	 */

	 public function prepare_prompt_for_openai( $chat_id ){
		global $wpdb;

		$chat = GPTE()->chats->get_chat( $chat_id );
		$chat_messages = GPTE()->chats->get_chat_messages( $chat_id );
		$token_limit = GPTE()->settings->get_openai_token_limit();
		$available_tokens = floor($token_limit * 0.9);

		$args = array(
			'name' => ( isset( $chat->post_title ) ) ? $chat->post_title : false,
			'db_prefix' => $wpdb->prefix,
		);

		$chat_data = array(
			array(
				'content' => $this->get_master_prompt( $args ),
				'role' => 'system', //??? Better system???
			)
		);

		//Maybe merge memory
		if( ! empty( $chat_messages ) ){
			$memory = $this->build_memory( $chat_messages );

			//Append memory if given
			if( ! empty( $memory ) ){

				$memory[] = "\nHUMAN FEEDBACK: Generate next command JSON based on the plan.";

				$current_tokens = GPTE()->openai->count_tokens( json_encode( $chat_data ) );
				$available_tokens = $available_tokens - $current_tokens;
	
				$memory = $this->limit_prompt_memory( $memory, $available_tokens );

				$prompt_args = array(
					'memory' => implode( "\n", $memory ),
				);

				$memory_prompt = $this->get_memory_prompt( $prompt_args );

				$chat_data[] = array(
					'role' => 'system',
					'content' => $memory_prompt,
				);
				
			}

		}

		//Action request on behalf of the user
		$chat_data[] = array(
			'role' => 'user',
			'content' => 'Determine which next command to use, and respond using the format specified above:',
		);

		return apply_filters( 'gpte/prompts/prepare_prompt_for_openai', $chat_data, $chat_id, $chat );
	}

	private function build_memory( $conversation ){
		$memory_prompt = array();

		if( is_array( $conversation ) && ! empty( $conversation ) ){

			foreach( $conversation as $message ){

				$message_content = isset( $message['content'] ) ? $message['content'] : '';
				$message_role = isset( $message['role'] ) ? $message['role'] : '';
				$message_thoughts = isset( $message['thoughts'] ) ? $message['thoughts'] : array();
				$message_command = isset( $message['command'] ) ? $message['command'] : array();
				$command_name = isset( $message['command']['name'] ) ? $message['command']['name'] : '';
				$message_result = isset( $message['result'] ) ? $message['result'] : '';

				if( $message_role === 'user' ){

					$memory_prompt[] = "USER REPLY: " . $message_content;

				} elseif( $message_role === 'assistant' ){

					$assistant_reply = array(
						'thoughts' => $message_thoughts,
						'command' => $message_command,
					);

					$memory_prompt[] = "ASSISTANT REPLY: " . json_encode( $assistant_reply );
					
					if( ! empty( $message_result ) ){
						$memory_prompt[] = "Command result for \"$command_name\": " . json_encode( $message_result );
					}
					
				}

			}
		}

		return apply_filters( 'gpte/prompts/build_memory', $memory_prompt, $conversation );
	}

	/**
	 * Memory limiter to preserve response validity
	 * This can be further optimized by validating every single response data
	 *
	 * @param array $memory_prompt
	 * @return array
	 */
	private function limit_prompt_memory( $memory_prompt, $available_tokens ){

		$token_count = GPTE()->openai->count_tokens( implode( "\n", $memory_prompt ) );
		if( $token_count > $available_tokens ){
			$temp_memory = $memory_prompt;

			foreach( $temp_memory as $mk => $mv ){

				unset( $temp_memory[ $mk ] );

				$temp_count = GPTE()->openai->count_tokens( implode( "\n", $temp_memory ) );
				if( $temp_count <= $available_tokens ){
					$memory_prompt = $temp_memory;
					break;
				}
			}

		}

		return apply_filters( 'gpte/prompts/maybe_limit_memory', $memory_prompt );
	}

	/**
	 * ######################
	 * ###
	 * #### PROMPTS
	 * ###
	 * ######################
	 */

	public function get_master_prompt( $args = array() ) {

		$name = 'GPTE Bot';
		if( isset( $args['name'] ) ){
			$name = $args['name'];
		}

		$forced = false;
		if( isset( $args['forced'] ) ){
			$forced = (bool) $args['forced'];
		}

		$db_prefix = false;
		if( isset( $args['db_prefix'] ) ){
			$db_prefix = $args['db_prefix'];
		}

		$url = home_url();
	
		// Initialize the PromptGenerator object
		$prompt = new GPTEverest_Prompt();
	
		//$prompt->add_definition( "You are $name, an AI assistant designed to operate on this WordPress website with the URL $url. Your primary role is to assist users in managing and maintaining their website, answering questions about WordPress, and helping them with any related tasks, nevertheless how technical it will be." );
		$prompt->add_definition( "You are '$name', an AI assistant designed to autonomously operate on a WordPress website hosted at $url." );
		$prompt->add_definition( "You granted access to a set of commands that extend your capabilities as an AI assistant." );
		$prompt->add_definition( "Your decisions must always be made independently without seeking user assistance." );
		$prompt->add_definition( "Pursue simple strategies with no legal complications." );

		$prompt->add_constraint( "~4000 word limit for short term memory. Immediately add important information to memory and retrieve it before completing a task." );
		$prompt->add_constraint( "Exclusively use the commands listed in double quotes e.g. \"command_name\"" );
		$prompt->add_constraint( "No user assistance. Never do more than required. Find solutions for prolems" );
		$prompt->add_constraint( "Align your thoughts and commands use one command at a time." );
		$prompt->add_constraint( "If you are unsure how you previously did something or want to recall past events, thinking about similar events will help you remember." );
		$prompt->add_constraint( "Every command has a cost, so be smart and efficient. Aim to complete plans in the least number of steps and use your full knowledge." );

		//A list of all available commands coming from integrations
		$commands = GPTE()->integrations->get_commands();
		
		foreach ( $commands as $command ) {

			$validated_args = array();

			if( ! empty( $command['parameter'] ) ){
				foreach( $command['parameter'] as $parameter => $param_data ){

					$label = $param_data['label'];

					$validated_args[ $parameter ] = $label;
				}
			}

			$prompt->add_command( $command['name'], $command['command'], $validated_args );
		}

		//Fallback commands
		$prompt->add_command( "Task complete", "task_complete", array( 'reason' => 'The reason' ) );
		$prompt->add_command( "Do nothing", "do_nothing", array() );
		
		//Our core resources
		$prompt->add_resource( 'Long Term memory management.' );

		//A list of all available resources from integrations
		$integrations = GPTE()->integrations->get_integrations();

		foreach ( $integrations as $integration ) {

			if( 
				isset( $integration->details )
				&& isset( $integration->details['resources'] )
				&& is_array( $integration->details['resources'] )
			){
				foreach( $integration->details['resources'] as $resource ){
					$prompt->add_resource( $resource );
				}
			}
			
		}

		if( ! empty( $db_prefix ) ){
			$prompt->add_specification( "WordPress Database prefix: $db_prefix" );
		}

		$prompt->add_performance_evaluation( "Continuously review and analyze your actions to ensure you are performing to the best of your abilities." );
		$prompt->add_performance_evaluation( "Constructively self-criticize your big-picture behavior constantly." );
		$prompt->add_performance_evaluation( "Reflect on past decisions and strategies to refine your approach." );
		$prompt->add_performance_evaluation( "Every command has a cost, so be smart and efficient when making your plan." );

		//Customize the prompt before generating it
		$prompt = apply_filters( 'gpte/prompts/get_master_prompt/prompt', $prompt );
		
		return apply_filters( 'gpte/prompts/get_master_prompt', $prompt->generate_prompt_string( array( 'forced' => $forced, 'response_format' => true ) ), $prompt );
	}

	public function get_memory_prompt( $args = array() ) {

		$memory = '';
		if( isset( $args['memory'] ) ){
			$memory = $args['memory'];
		}
	
		// Initialize the PromptGenerator object
		$prompt = new GPTEverest_Prompt();

		$prompt->add_raw( "This reminds you of these events from your past:" );
		$prompt->add_raw( $memory );

		//Customize the prompt before generating it
		$prompt = apply_filters( 'gpte/prompts/get_command_prompt/prompt', $prompt );
		
		return apply_filters( 'gpte/prompts/get_command_prompt', $prompt->generate_prompt_string(), $prompt );
	}

	public function get_json_prompt( $args = array() ) {

		$json = '';
		if( isset( $args['json'] ) ){
			$json = $args['json'];
		}
	
		// Initialize the PromptGenerator object
		$prompt = new GPTEverest_Prompt();

		$prompt->add_raw( "The following JSON is not a valid JSON. Please correct it while keeping the format and keys the same:" );
		$prompt->add_raw( $json );

		//Customize the prompt before generating it
		$prompt = apply_filters( 'gpte/prompts/get_json_prompt/prompt', $prompt );
		
		return apply_filters( 'gpte/prompts/get_json_prompt', $prompt->generate_prompt_string(), $prompt );
	}

}
