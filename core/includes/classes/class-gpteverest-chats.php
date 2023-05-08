<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Gpteverest_Chats
 *
 * This class contains the logic for chats
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_Chats
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Chats{

	private $chat_cache = array();

	/**
	 * Execute class-specific logic after the plugin is loaded
	 *
	 * @return void
	 */
	public function execute(){
		add_action( 'wp_ajax_gpte_chats_handler',  array( $this, 'gpte_chats_handler' ) );
	}

	/**
	 * ######################
	 * ###
	 * #### AJAX HANDLERS
	 * ###
	 * ######################
	 */

	/**
	 * Manage GPTE Chats
	 *
	 * @return void
	 */
	public function gpte_chats_handler() {
		check_ajax_referer( md5( GPTE_SLUG ), 'gpte_nonce' );

		$chat_handler = isset( $_REQUEST['handler'] ) ? sanitize_title( $_REQUEST['handler'] ) : '';
		$response = array( 'success' => false );

		if ( empty( $chat_handler ) ) {
			$response['msg'] = __( 'There was an issue localizing the remote data', 'gpte' );
			return $response;
		}

		switch( $chat_handler ) {
			case 'delete_chat':
				$chat_id = isset( $_REQUEST['chat_id'] ) ? intval( $_REQUEST['chat_id'] ) : 0;

				$deleted = $this->delete_chat( $chat_id );

				if( $deleted ){
					$response['success'] = true;
					$response['msg'] = __( 'The chat has been successfully deleted.', 'gpte' );
				} else {
					$response['msg'] = __( 'An error occured while deleting the chat.', 'gpte' );
				}
				
				break;
		}

		if( $response['success'] ){
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}

		die();
	}

	/**
	 * ######################
	 * ###
	 * #### LIST TABLE DEFINITIONS
	 * ###
	 * ######################
	 */

	/**
	 * Get the customized list class for the Flows
	 *
	 * @since 1.0.0
	 *
	 * @return GPTE_WP_List_Table
	 */
	public function get_chat_lists_table_class(){

		$args = array(
			'labels' => array(
				'singular' => __( 'chat', 'gpte' ),
				'plural' => __( 'chats', 'gpte' ),
				'search_placeholder' => __( 'ID/Name...', 'gpte' ),
			),
			'columns' => array(
				'id' => array(
					'label' => __( 'ID + Chat name', 'gpte' ),
					'callback' => array( $this, 'chats_lists_cb_title' ),
					'actions_callback' => array( $this, 'chats_lists_cb_title_actions' ),
					'sortable' => 'ASC',
				),
				'agents' => array(
					'label' => __( 'Agents', 'gpte' ),
					'callback' => array( $this, 'chats_lists_cb_agents' ),
					'sortable' => 'ASC',
				),
				'date' => array(
					'label' => __( 'Created', 'gpte' ),
					'callback' => array( $this, 'chats_lists_cb_date' ),
					'sortable' => 'ASC',
				),
			),
			'settings' => array(
				'per_page' => 20,
				'default_order_by' => 'id',
				'default_order' => 'DESC',
				'show_search' => true,
			),
			'item_filter' => array( $this, 'chats_lists_filter_items' ),
		);

		$table = GPTE()->lists->new_list( $args );

		return $table;
	}

	/**
	 * The callback for the chats list table title
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function chats_lists_cb_title( $item, $column_name, $column ){
		$content = '';
		$current_url = GPTE()->helpers->get_current_url( false, true );
		$title = '#' . $item->ID . ' - ' . $item->post_title;
		$edit_link = GPTE()->helpers->built_url( $current_url, array_merge( $_GET, array( 'chat_id' => $item->ID, ) ) );

		$content = sprintf(
			'<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			esc_attr( sprintf(
				__( 'Edit &#8220;%s&#8221;', 'gpte' ),
				$title
			) ),
			esc_html( $title )
		);

		$content = sprintf( '<strong>%s</strong>', $content );

		return $content;
	}

	/**
	 * The callback for the title item of the Flows list
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param bool $primary
	 * @param array $column
	 * @return array
	 */
	public function chats_lists_cb_title_actions( $item, $column_name, $primary, $column ){

		$current_url = GPTE()->helpers->get_current_url( false, true );
		$edit_url = GPTE()->helpers->built_url( $current_url, array_merge( $_GET, array( 'chat_id' => $item->ID, ) ) );
		$edit_title = __( 'Open', 'gpte' );
		$delete_title = __( 'Delete', 'gpte' );

		$actions = array(
			'edit' => GPTE()->helpers->create_link( 
				$edit_url, 
				$edit_title,
				array(
					'title' => $edit_title,
				)
			),
			'delete' => GPTE()->helpers->create_link( 
				'', 
				$delete_title,
				array(
					'class' => 'text-error gpte-delete-chat-template',
					'title' => $delete_title,
					'data-gpte-chat-id' => $item->ID,
				)
			),
		);

		return $actions;
	}

	/**
	 * The callback for the chats list table agents count
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function chats_lists_cb_agents( $item, $column_name, $column ){
		$content = '';

		$query_args = array(
			'post_type' => 'gptechats',
			'post_parent' => $item->ID,
		);

		$query = new WP_Query( $query_args );

		if( ! empty( $query ) && isset( $query->posts ) ){
			$content = $query->found_posts;
		}

		return $content;
	}

	/**
	 * The callback for the chats list table date
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function chats_lists_cb_date( $item, $column_name, $column ){
		return GPTE()->helpers->get_formatted_date( $item->post_date, 'F j, Y, g:i a' );
	}

	/**
	 * The callback with query arguments to filter the chat logs
	 * 
	 * @since 1.0.0	
	 * @param array $args
	 * @return void
	 */
	public function chats_lists_filter_items( $args ){
		$item_data = array(
			'items' => array(),
			'total' => 0,
		);

		$query_args = array(
			'post_type' => 'gptechats',
			'post_parent' => 0, //Only top level
		);

		$query_args = array_merge( $query_args, $args );

		$query = new WP_Query( $query_args );

		if( ! empty( $query ) && isset( $query->posts ) ){
			$item_data['items'] = $query->posts;
			$item_data['total'] = $query->found_posts;
		}

		return $item_data;
	}

	/**
	 * ######################
	 * ###
	 * #### AGENT LIST TABLE DEFINITIONS
	 * ###
	 * ######################
	 */

	/**
	 * Get the customized list class for the Flows
	 *
	 * @since 1.0.0
	 *
	 * @return GPTE_WP_List_Table
	 */
	public function get_agents_lists_table_class(){

		$args = array(
			'labels' => array(
				'singular' => __( 'agent', 'gpte' ),
				'plural' => __( 'agents', 'gpte' ),
				'search_placeholder' => __( 'ID/Name...', 'gpte' ),
			),
			'columns' => array(
				'id' => array(
					'label' => __( 'ID + Agent name', 'gpte' ),
					'callback' => array( $this, 'agents_lists_cb_title' ),
					'actions_callback' => array( $this, 'agents_lists_cb_title_actions' ),
					'sortable' => 'ASC',
				),
				'chats' => array(
					'label' => __( 'Connected Chats', 'gpte' ),
					'callback' => array( $this, 'agents_lists_cb_chats' ),
					'sortable' => 'ASC',
				),
				'date' => array(
					'label' => __( 'Created', 'gpte' ),
					'callback' => array( $this, 'agents_lists_cb_date' ),
					'sortable' => 'ASC',
				),
			),
			'settings' => array(
				'per_page' => 20,
				'default_order_by' => 'id',
				'default_order' => 'DESC',
				'show_search' => true,
			),
			'item_filter' => array( $this, 'agents_lists_filter_items' ),
		);

		$table = GPTE()->lists->new_list( $args );

		return $table;
	}

	/**
	 * The callback for the agents list table title
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function agents_lists_cb_title( $item, $column_name, $column ){
		$content = '';
		$current_url = GPTE()->helpers->get_current_url( false, true );
		$master_chat = GPTE()->chats->get_chat_by_agent( (int) $item->post_parent );
		$title = '#' . $item->ID . ' - ' . $item->post_title;
		$edit_link = GPTE()->helpers->built_url( $current_url, array_merge( $_GET, array( 'chat_id' => $master_chat->ID, 'agent_id' => $item->ID ) ) );

		$content = sprintf(
			'<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			esc_attr( sprintf(
				__( 'Open &#8220;%s&#8221;', 'gpte' ),
				$title
			) ),
			esc_html( $title )
		);

		$content = sprintf( '<strong>%s</strong>', $content );

		return $content;
	}

	/**
	 * The callback for the title item of the agents
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param bool $primary
	 * @param array $column
	 * @return array
	 */
	public function agents_lists_cb_title_actions( $item, $column_name, $primary, $column ){

		$current_url = GPTE()->helpers->get_current_url( false, true );
		$master_chat = GPTE()->chats->get_chat_by_agent( (int) $item->post_parent );
		$edit_url = GPTE()->helpers->built_url( $current_url, array_merge( $_GET, array( 'chat_id' => $master_chat->ID, 'agent_id' => $item->ID ) ) );
		$edit_title = __( 'Open', 'gpte' );
		$delete_title = __( 'Delete', 'gpte' );

		$actions = array(
			'edit' => GPTE()->helpers->create_link( 
				$edit_url, 
				$edit_title,
				array(
					'title' => $edit_title,
				)
			),
			'delete' => GPTE()->helpers->create_link( 
				'', 
				$delete_title,
				array(
					'class' => 'text-error gpte-delete-chat-template',
					'title' => $delete_title,
					'data-gpte-chat-id' => $item->ID,
				)
			),
		);

		return $actions;
	}

	/**
	 * The callback for the agents list table chat count
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function agents_lists_cb_chats( $item, $column_name, $column ){

		$current_url = GPTE()->helpers->get_current_url( false, true );
		$master_chat = GPTE()->chats->get_chat_by_agent( (int) $item->ID );
		$edit_link = GPTE()->helpers->built_url( $current_url, array_merge( $_GET, array( 'chat_id' => $master_chat->ID ) ) );

		$link = sprintf(
			'<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			esc_attr( sprintf(
				__( 'Visit &#8220;%s&#8221;', 'gpte' ),
				$master_chat->post_title 
			) ),
			esc_html( '#' . $master_chat->ID )
		);

		$content = $link . ' ' . $master_chat->post_title;

		return $content;
	}

	/**
	 * The callback for the agents list table date
	 *
	 * @since 1.0.0
	 * @param object $item
	 * @param string $column_name
	 * @param array $column
	 * @return string
	 */
	public function agents_lists_cb_date( $item, $column_name, $column ){
		return GPTE()->helpers->get_formatted_date( $item->post_date, 'F j, Y, g:i a' );
	}

	/**
	 * The callback with query arguments to filter the agents
	 * 
	 * @since 1.0.0	
	 * @param array $args
	 * @return void
	 */
	public function agents_lists_filter_items( $args ){
		$item_data = array(
			'items' => array(),
			'total' => 0,
		);

		$query_args = array(
			'post_type' => 'gptechats',
			'post_parent__not_in' => array(0), //Only childs allowe
		);

		$query_args = array_merge( $query_args, $args );

		$query = new WP_Query( $query_args );

		if( ! empty( $query ) && isset( $query->posts ) ){
			$item_data['items'] = $query->posts;
			$item_data['total'] = $query->found_posts;
		}

		return $item_data;
	}

	/**
	 * ######################
	 * ###
	 * #### CORE FUNCTIONS
	 * ###
	 * ######################
	 */

	public function is_automode(){
		$is_automode = false;

		if( isset( $_GET['automode'] ) && $_GET['automode'] === 'yes' ){
			$is_automode = true;
		}

		return apply_filters( 'gpte/chat/is_automode', $is_automode );
	}

	public function get_starter_messages(){
		$messages = array(
			array(
				'content' => 'Welcome to <strong>GPTEverest</strong>!',
				'role' => 'assistant',
			),
			array(
				'content' => 'How can I help you?',
				'role' => 'assistant',
			),
		);

		return apply_filters( 'gpte/chat/get_starter_messages', $messages );
	}

	public function create_chat( $args = array() ){
		$default_args = array(
			'post_type' => 'gptechats',
			'post_status' => 'publish',
		);

		$args = array_merge( $default_args, $args );
		
		$created = wp_insert_post( $args );

		return apply_filters( 'gpte/chat/create_chat', $created, $args );
	}

	public function delete_chat( $chat_id ){
		$deleted = false;

		if( ! empty( $chat_id ) ){
			$deleted = wp_delete_post( $chat_id, true );

			if( $deleted ){
				if( isset( $this->chat_cache[ $chat_id ] ) ){
					unset( $this->chat_cache[ $chat_id ] );
				}
			}
		}

		return apply_filters( 'gpte/chat/delete_chat', $deleted, $chat_id );
	}

	//Todo - build a smart caching logic
	public function get_chat( $chat_id, $args = array() ){
		$chat = null;
		
		if( ! empty( $chat_id ) ){

			if( isset( $this->chat_cache[ $chat_id ] ) ){
				$chat = $this->chat_cache[ $chat_id ];
			} else {
				$chat_post = get_post( $chat_id );

				if( ! empty( $chat_post ) && ! is_wp_error( $chat_post ) ){
					$chat = $chat_post;
					$this->chat_cache[ $chat_id ] = $chat_post;
				} 
			}
			
		}

		return apply_filters( 'gpte/chat/get_chat_messages', $chat, $args );
	}

	/**
	 * Get a chat based on an agent
	 * This will get the master chat of all agents, 
	 * nevertheless how nested it is.
	 *
	 * @param int $agent_id
	 * @param array $args
	 * @return mixed
	 */
	public function get_chat_by_agent( $agent_id, $args = array() ){
		$chat = null;
		$max_level = ( isset( $args['max_level'] ) ) ? intval( $args['max_level'] ) + 1 : -1; //0 = infinite
		
		if( ! empty( $agent_id ) ){
			$chat = $this->locate_master_chat( $agent_id, $max_level );
		}

		return apply_filters( 'gpte/chat/get_chat_messages', $chat, $args );
	}

	private function locate_master_chat( $agent_id, $max_level ){
		$master_chat = null;
		$max_level--;
		
		$chat = get_post( $agent_id );

		if( ! empty( $chat ) ){

			if( 
				isset( $chat->post_parent )
				&& ! empty( $chat->post_parent )
				&& ( $max_level < 0 || $max_level > 0 )
			){
				$master_chat = $this->locate_master_chat( $chat->post_parent, $max_level );
			} else {
				$master_chat = $chat;
			}

		}
		

		return $master_chat;
	}

	public function get_chat_agents( $chat_id, $args = array() ){
		$agents = $this->get_child_agents( $chat_id );	

		return apply_filters( 'gpte/chat/get_chat_messages', $agents, $chat_id, $args );
	}

	private function get_child_agents( $chat_id ){
		$agents = array();

		if( ! empty( $chat_id ) ){
			$agent_args = array(
				'post_type' => 'gptechats',
				'post_parent' => $chat_id,
				'post_status' => 'publish',
			);

			$agents_query = get_posts( $agent_args );

			if( ! empty( $agents_query ) ){
				foreach( $agents_query as $agent ){
					$agents[ $agent->ID ] = $agent;
					
					$sub_agents = $this->get_child_agents( $agent->ID );
					if( ! empty( $sub_agents ) ){
						foreach( $sub_agents as $sa ){
							$agents[ $sa->ID ] = $sa;
						}
					}
				}
			}
		}

		return $agents;
	}

	public function get_chat_messages( $chat_id, $args = array() ){
		$messages_validated = array();
		$commands = GPTE()->integrations->get_commands();
		
		if( ! empty( $chat_id ) ){
			$chat = $this->get_chat( $chat_id );

			if( 
				! is_wp_error( $chat ) 
				&& ! empty( $chat->post_content ) 
				&& GPTE()->helpers->is_json( $chat->post_content )
			){
				$messages = json_decode( $chat->post_content, true );
				
				$messages_validated = array_merge( $messages_validated, $messages );

				foreach( $messages_validated as $msg_key => $msg_data ){

					//Command
					if( isset( $args['add_command_details'] ) && $args['add_command_details'] ){
						if( isset( $msg_data['command'] ) ){
							if( isset( $msg_data['command']['name'] ) ){
								$command_name = $msg_data['command']['name'];
	
								$commands = GPTE()->integrations->get_commands();
								
								if( isset( $commands[ $command_name ] ) ){
									$messages_validated[ $msg_key ]['command_details'] = $commands[ $command_name ];
								}
							}
						}
					}

				}
			}
		}

		return apply_filters( 'gpte/chat/get_chat_messages', $messages_validated );
	}

	public function process_message( $chat_id, $message, $args = array() ){
		$response = array( 'success' => true );

		$add_command_details = false;
		if( isset( $args['add_command_details'] ) ){
			$add_command_details = (bool) $args['add_command_details'];
		}
		
		if( ! is_array( $message ) ){
			$message = array( 
				'role' => 'user',
				'content' => $message,
			);
		}

		$added = $this->core_add_message( $chat_id, $message );

		if ( is_numeric( $added ) ) {

			//Maybe handle previous intent before continuing
			$this->maybe_handle_intent( $chat_id, $message['content'] );

			$answer = GPTE()->prompts->send_prompt( $chat_id );
	
			if(
				is_array( $answer )
			){

				$message_content = $answer['data']['content'];

				if(
					isset( $answer['success'] )
					&& $answer['success']
				){

					$bot_args = array( 
						'role' => $answer['data']['role'],
						'content' => $message_content,
					);

					if( isset( $answer['data']['thoughts'] ) ){
						$bot_args['thoughts'] = $answer['data']['thoughts'];
					}

					if( isset( $answer['data']['command'] ) ){
						$bot_args['command'] = $answer['data']['command'];
					}

					$message = $this->core_add_message( $chat_id, $bot_args );
					$chat_message_args = array();

					if( $add_command_details ){
						$chat_message_args['add_command_details'] = $add_command_details;
					}

					$message_data = array(
						'index' => $message,
						'message' => $this->get_chat_messages( $chat_id, $chat_message_args )[ $message ],
					);

					$response['msg'] = __( 'The answer was successfully retrieved.', 'gpteverest' );
					$response['answer'] = $message_content;
					$response['data'] = $message_data;

				} else {
					$response['msg'] = __( 'A response was given, but ni success was returned.', 'gpteverest' );
					$response['answer'] = $message_content;
				}
				
			} else {
				$response['msg'] = __( 'No successful response given.', 'gpteverest' );
				$response['answer'] = __( 'Something went wrong. Please try again.', 'gpteverest' );
			}
			
		} else {
			$response['msg'] = __( 'Adding the chat message failed.', 'gpteverest' );
			$response['answer'] = __( 'An error occured while adding the message.', 'gpteverest' );
		}

		return apply_filters( 'gpte/chat/add_message', $response, $chat_id, $message );
	}

	private function core_add_message( $chat_id, $args = array() ){
		$added = false;
	
		if( empty( $chat_id ) ){
			return $added;
		}

		$chat_id = intval( $chat_id );
		$chat = $this->get_chat_messages( $chat_id );

		//todo - somehow validate all necessay fields, including the thoughts and command
		$message_array = array(
			'content' => isset( $args['content'] ) ? $args['content'] : '',
			'role' => isset( $args['role'] ) ? $args['role'] : 'assistant',
			'thoughts' => isset( $args['thoughts'] ) ? $args['thoughts'] : array(),
			'command' => isset( $args['command'] ) ? $args['command'] : false,
			'result' => isset( $args['result'] ) ? $args['result'] : '',
		);

		$chat[] = $message_array;

		$chat = array_values( $chat );

		$chat_args = array(
			'content' => json_encode( $chat ),
		);	

		$added = $this->update_chat_data( $chat_id, $chat_args );
		
		if( $added ){
			$added = count( $chat ) - 1;
		}

		return apply_filters( 'gpte/chat/core_add_message', $added, $chat_id );
	}

	public function update_message( $chat_id, $message_key, $args = array() ){
		$added = false;
	
		if( empty( $chat_id ) || ! is_numeric( $message_key ) ){
			return $added;
		}

		$message_key = intval( $message_key );
		$chat_id = intval( $chat_id );
		$chat = $this->get_chat_messages( $chat_id );

		if( isset( $chat[ $message_key ] ) ){
			$message = $chat[ $message_key ];

			if( isset( $args['content'] ) ){
				$message['content'] = $args['content'];
			}

			if( isset( $args['role'] ) ){
				$message['role'] = $args['role'];
			}

			if( isset( $args['thoughts'] ) ){
				$message['thoughts'] = $args['thoughts'];
			}

			if( isset( $args['command'] ) ){
				$message['command'] = $args['command'];
			}

			if( isset( $args['result'] ) ){
				$message['result'] = $args['result'];
			}

			if( isset( $args['type'] ) ){
				$message['type'] = $args['type'];
			}

			$chat[ $message_key ] = $message;

			$chat = array_values( $chat );

			$chat_args = array(
				'content' => json_encode( $chat ),
			);	

			$added = $this->update_chat_data( $chat_id, $chat_args );
		}

		return apply_filters( 'gpte/chat/add_message', $added, $chat_id );
	}

	private function update_chat_data( $chat_id, $args = array() ){
		$updated = false;

		if( empty( $chat_id ) || empty( $args ) ){
			return $updated;
		}
		
		$args = apply_filters( 'gpte/chat/update_chat_data/args', $args, $chat_id );

		$chat_id = intval( $chat_id );

		$chat_args = array(
			'ID' => $chat_id,
		);

		if( isset( $args['content'] ) ){
			$chat_args['post_content'] = addslashes( $args['content'] ); // Necessary as otherwise WordPress breaks JSONS
		}

		$updated = wp_update_post( $chat_args );

		if( $updated && isset( $this->chat_cache[ $chat_id ] ) ){
			unset( $this->chat_cache[ $chat_id ] );
		}

		return apply_filters( 'gpte/chat/update_chat_data', $updated, $chat_id, $args );
	}

	public function is_intent( $chat_id, $message ){
		$return = false;

		$message = strtolower( $message );

		if( $message === 'yes' ){
			$chat = $this->get_chat_messages( $chat_id );
			$index = count( $chat ) - 2;

			//Get the second-last element as the last is the intent
			if( isset( $chat[ $index ] ) ){
				$slast_message = $chat[ $index ];

				//Make sure it really is an intent, otherwise bail
				if(
					is_array( $slast_message )
					&& isset( $slast_message['command'] )
					&& isset( $slast_message['command']['name'] )
					&& ! empty( $slast_message['command']['name'] )
				){
					$return = array(
						'index' => $index,
						'content' => $slast_message,
					);
				}
			}
		}

		return apply_filters( 'gpte/chat/is_intent', $return, $chat_id, $message );
	}

	public function get_chat_type( $chat_id ){
		$type = 'chat';

		$chat = $this->get_chat( $chat_id );

		//Only agents are parents
		if( isset( $chat->post_parent ) && $chat->post_parent ){
			$type = 'agent';
		}

		return apply_filters( 'gpte/chat/get_chat_type', $type, $chat_id );
	}

	public function maybe_handle_intent( $chat_id, $message ){

		$slast_message = $this->is_intent( $chat_id, $message );

		//Bail if not an intent
		if( $slast_message === false ){
			return false;
		}

		$response = array(
			'success' => false,
			'msg' => __( 'The command was not executed.', 'gpteverest' ),
			'data' => array()
		);

		do_action( 'gpte/chat/maybe_handle_intent/before', $chat_id, $message );

		if( 
			isset( $slast_message['content']['command'] )
			&& is_array( $slast_message['content']['command'] )
			&& isset( $slast_message['content']['command']['name'] )
		){

			$args = isset( $slast_message['content']['command']['args'] ) ? $slast_message['content']['command']['args'] : array();	

			//Append the chat ID
			$args['chat_id'] = $chat_id;

			$command_name = $slast_message['content']['command']['name'];

			switch( $command_name ){
				case 'task_complete':

					if( ! empty( $chat_id ) ){
						$response['success'] = true;
						$response['msg'] = __( 'Task successfully completed.', 'gpteverest' );
						$response['data'] = $args;
					} else {
						$response['msg'] = __( 'No chat ID found.', 'gpteverest' );
					}
					
					break;
				case 'do_nothing':

					$response['msg'] = __( 'Nothing.', 'gpteverest' );
					
					break;
				default:
				$response = GPTE()->integrations->safe_execute_command( $response, $command_name, $args );
			}

			

			$this->update_message( $chat_id, $slast_message['index'], array( 'result' => $response ) );
		}

		do_action( 'gpte/chat/maybe_handle_intent/after', $chat_id, $message );

		return apply_filters( 'gpte/chat/update_chat_data', $response, $chat_id, $slast_message );
	}

}
