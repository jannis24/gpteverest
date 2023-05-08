<?php

/**
 * Gpteverest_Prompt Class
 *
 * This class for a single prompt design. Inspired by AutoGPT (Thanks!)
 *
 * @since 1.0.0
 */

/**
 * The Prompt class
 *
 * @package		GPTE
 * @subpackage	Classes/Gpteverest_Prompt
 * @author		Jannis Thuemmig
 * @since		1.0.0
 */
class GPTEverest_Prompt {

	public function __construct() {
		$this->raw                   = array();
		$this->definition            = array();
		$this->constraints           = array();
		$this->results               = array();
		$this->commands              = array();
		$this->resources             = array();
		$this->specifications        = array();
		$this->performance_evaluation = array();
		$this->response_format       = array(
			'thoughts' => array(
				'text'      => 'thought',
				'reasoning' => 'reasoning',
				'plan'      => "- short bulleted\n- list that conveys\n- long-term plan",
				'criticism' => 'constructive self-criticism',
				'speak'     => '(Required) thoughts summary to say to user',
			),
			'command'  => array(
				'name' => 'command_name',
				'args' => array(
					'arg_name' => 'value',
				),
			),
		);
	}

	public function add_raw( $raw ) {
		$this->raw[] = $raw;
	}

	public function add_definition( $definition ) {
		$this->definition[] = $definition;
	}

	public function add_constraint( $constraint ) {
		$this->constraints[] = $constraint;
	}

	public function add_result( $result ) {
		$this->results[] = $result;
	}

	public function add_command( $command_label, $command_name, $args = null ) {
		if ( is_null( $args ) ) {
			$args = array();
		}

		$command_args = array_combine( array_keys( $args ), array_values( $args ) );

		$command = array(
			'label' => $command_label,
			'name'  => $command_name,
			'args'  => $command_args,
		);

		$this->commands[] = $command;
	}

	private function generate_command_string( $command ) {

		$arguments = json_encode( $command['args'] );

		return "{$command['label']}: \"{$command['name']}\", args: {$arguments}";
	}

	public function add_resource( $resource ) {
		$this->resources[] = $resource;
	}

	public function add_specification( $specification ) {
		$this->specifications[] = $specification;
	}

	public function add_performance_evaluation( $evaluation ) {
		$this->performance_evaluation[] = $evaluation;
	}

	private function generate_numbered_list( $items, $item_type = 'list' ) {
		$list = array();

		foreach ( $items as $index => $item ) {
			if ( $item_type === 'command' ) {
				$list[] = ( $index + 1 ) . '. ' . $this->generate_command_string( $item );
			} elseif( $item_type === 'line' ){
				$list[] = $item;
			} else {
				$list[] = ( $index + 1 ) . '. ' . $item;
			}
		}

		return implode( "\n", $list );
	}

	private function prepare_result( $results ) {

		foreach ( $results as $index => $result ) {

			if( is_string( $result ) ){
				$results[ $index ] = $result;
			} if(
				is_object( $result )
				|| is_array( $result )
			){
				$results[ $index ] = json_encode( $result );
			}

		}

		return implode( "\n", $results );
	}

	public function generate_prompt_string( $args = array() ) {

		$response_format = false;
		if( isset( $args['response_format'] ) ){
			$response_format = (bool) $args['response_format'];
		}
		
		$prompt = '';

		if ( ! empty( $this->raw ) ) {
			$prompt .= $this->generate_numbered_list( $this->raw, 'line' ) . "\n\n";
		}

		if ( ! empty( $this->definition ) ) {
			$prompt .= "Definition:\n" . $this->generate_numbered_list( $this->definition ) . "\n\n";
		}

		if ( ! empty( $this->constraints ) ) {
			$prompt .= "Constraints:\n" . $this->generate_numbered_list( $this->constraints ) . "\n\n";
		}

		if ( ! empty( $this->commands ) ) {
			$prompt .= "Commands:\n" . $this->generate_numbered_list( $this->commands, 'command' ) . "\n\n";
		}

		if ( ! empty( $this->results ) ) {
			$prompt .= "Result:\n" . $this->prepare_result( $this->results ) . "\n\n";
		}

		if ( ! empty( $this->resources ) ) {
			$prompt .= "Resources:\n" . $this->generate_numbered_list( $this->resources ) . "\n\n";
		}

		if ( ! empty( $this->specifications ) ) {
			$prompt .= "Specifications:\n" . $this->generate_numbered_list( $this->specifications ) . "\n\n";
		}

		if ( ! empty( $this->performance_evaluation ) ) {
			$prompt .= "Your performance evaluation:\n" . $this->generate_numbered_list( $this->performance_evaluation ) . "\n\n";
		}

		if( $response_format ){
			$default_format = $this->response_format;
			$formatted_response_format = json_encode( $default_format, JSON_PRETTY_PRINT );
			
			$prompt .= "Only respond in the JSON format below: \nResponse Format: \n{$formatted_response_format} \nEnsure the response uses JSON and can be parsed by the PHP function json_decode().";
		}

		return apply_filters( 'gpte/prompt/generate_prompt_string', $prompt, $args );
	}

}
