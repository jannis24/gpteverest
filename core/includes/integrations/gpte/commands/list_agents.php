<?php
if ( ! class_exists( 'GPTE_Integrations_gpte_Commands_list_agents' ) ) :
	/**
	 * Load the list_agents command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_gpte_Commands_list_agents {

		public function get_details() {

			$parameter = array();

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
				'command'            => 'list_agents', // required
				'name'              => __( 'List GPT Agents', 'gpte' ),
				'parameter'         => $parameter,
				'returns'           => $returns,
				'returns_code'      => $returns_code,
				'short_description' => __( 'List all of the GPT Agents on the first layer.', 'gpte' ),
				'description'       => array(),
				'integration'       => 'gpte',
				'premium'           => true,
			);

		}

		public function execute( $return_args, $data ) {
			
			$chat_id = GPTE()->helpers->get_data( $data, 'chat_id' );
			
			if( empty( $chat_id ) ){
				$return_args['msg']     = __( 'An error occured. No related chat ID found.', 'gpte' );
				return $return_args;
			}

			$parent_chat = GPTE()->chats->get_chat_by_agent( $chat_id, array( 'max_level' => 1 ) );

			$posts = null;

			if( ! empty( $parent_chat ) && isset( $parent_chat->ID ) ){
				$args = array(
					'post_type' => 'gptechats',
					'post_status' => 'publish',
					'post_parent' => $parent_chat->ID,
				);

				//If an agent calls, exclude the current one to not cause loops
				if( $parent_chat->ID !== $chat_id ){
					$args['post__not_in'] = array( $chat_id );
				}

				$posts = new WP_Query( $args );
			}
	
			if( ! empty( $posts ) && isset( $posts->posts ) && ! empty( $posts->posts ) ){

				$agent_list = array();

				foreach( $posts->posts as $post ){
					$agent_list[] = array(
						'agent_id' => $post->ID,
						'task' => $post->post_excerpt,
					);
				}

				$return_args['success'] = true;
				$return_args['msg'] = __( 'The agent have been successfully listed.', 'gpte' );
				$return_args['data']['total_agents'] = $posts->found_posts;
				$return_args['data']['agents'] = $agent_list;
			} else {
				$return_args['msg'] = __( 'No agents given.', 'gpte' );
				
			}

			return $return_args;

		}
	}
endif;
