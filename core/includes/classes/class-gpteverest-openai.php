<?php

/**
 * Gpteverest_OpenAI Class
 *
 * This class contains all of the OpenAI functionality
 *
 * @since 1.0.0
 */

/**
 * The OpenAI class of the plugin.
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_OpenAI
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_OpenAI {

    public $apiurl = 'https://api.openai.com';
    public $apikey = '';
    public $apimodel = '';

    public function execute(){
        $this->apikey = get_option( 'gpte_openai_api_key' );
        $this->apimodel = get_option( 'gpte_openai_api_model' );

        if( empty( $this->apimodel ) ){
            $this->apimodel = 'gpt-3.5-turbo';
        }
    }

	public function request_completion( $args = array() ){
        $return = '';
        $completions_url = $this->apiurl . '/v1/chat/completions';

        $body = array(
            'model' => isset( $args['model'] ) ? $args['model'] : $this->apimodel,
            'prompt' => isset( $args['prompt'] ) ? $args['prompt'] : '',
            'max_tokens' => isset( $args['max_tokens'] ) ? $args['max_tokens'] : 2000,
            'n' => isset( $args['n'] ) ? $args['min_length'] : 1,
        );

        $post_args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apikey,
            ),
            'body' => json_encode( $body ),
            'timeout' => 30,
        );

        $response = wp_remote_post( $completions_url, $post_args );
        $body = wp_remote_retrieve_body( $response );  

        $body_array = json_decode( $body, true );
        if( 
            is_array( $body_array ) 
            && isset( $body_array['choices'] )
            && isset( $body_array['choices'][0] )
            && isset( $body_array['choices'][0]['text'] )
        ){
            $return = $body_array['choices'][0]['text'];
        }

        return $return;
    }

	public function request_chat_completion( $args = array() ){
        $return = array(
            'success' => false,
            'msg' => '',
            'data' => array(),
        );
        $chat_completions_url = $this->apiurl . '/v1/chat/completions';

        $body = array(
            'model' => isset( $args['model'] ) ? $args['model'] : $this->apimodel,
            'messages' => isset( $args['messages'] ) ? array_values( $args['messages'] ) : array(),
            'temperature' => isset( $args['temperature'] ) ?  $args['temperature'] : 0,
        );

        $post_args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apikey,
            ),
            'body' => json_encode( $body ),
            'timeout' => 60,
        );

        $response = wp_remote_post( $chat_completions_url, $post_args );
        $result_body = wp_remote_retrieve_body( $response );  
         
        $body_array = json_decode( $result_body, true );  
        ob_start();
        var_dump('afterrrrrrrrrr');
        var_dump($post_args);
        var_dump($body_array);
        var_dump($result_body);
        $res = ob_get_clean();
        error_log($res);    
        if( 
            is_array( $body_array ) 
            && isset( $body_array['choices'] )
            && isset( $body_array['choices'][0] )
            && isset( $body_array['choices'][0]['message'] )
        ){
            $return['success'] = true;
            $return['msg'] = __( 'The chat completion was successfully retrieved.', 'gpteverest' );
            $return['data'] = $body_array['choices'][0]['message'];
        } elseif(
            is_array( $body_array ) 
            && isset( $body_array['error'] )
        ){
            $return['msg'] = __( 'An error occured while fetching the chat completion.', 'gpteverest' );
            $return['data'] = $body_array['error'];
        }

        return $return;
    }

    public function content_max_tokens( $string, $max_tokens, $cut_from_end = true ) {

        $current_tokens = $this->count_tokens( $string );
        if ( $current_tokens > $max_tokens ) {
            $ratio = $max_tokens / $current_tokens;
            $shortened_length = floor( mb_strlen( $string, 'UTF-8' ) * $ratio );
        
            if ( $cut_from_end ) {
                $string = mb_substr( $string, 0, $shortened_length, 'UTF-8' );
            } else {
                $start_position = mb_strlen( $string, 'UTF-8' ) - $shortened_length;
                $string = mb_substr( $string, $start_position, $shortened_length, 'UTF-8' );
            }
        }

        return apply_filters( 'gpte/settings/content_max_tokens', $string, $max_tokens, $cut_from_end );
    }

    public function count_tokens( $string ) {

        $text_length = mb_strlen( $string, 'UTF-8' );
        $approx_tokens = ceil( $text_length / 4 );

        return apply_filters( 'gpte/settings/count_tokens', $approx_tokens, $string );
    }

}
