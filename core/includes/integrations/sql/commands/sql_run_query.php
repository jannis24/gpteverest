<?php
if ( ! class_exists( 'GPTE_Integrations_sql_Commands_sql_run_query' ) ) :
	/**
	 * Load the sql_run_query command
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_sql_Commands_sql_run_query {

		public function get_details() {
			$parameter         = array(
				'sql_query' => array(
					'required'          => true,
					'label'             => __( 'The SQL Query', 'gpte' ),
					'short_description' => __( '(String) The SQL queries you would like to run.', 'gpte' ),
				),
			);

			$returns           = array(
				'success' => array( 'short_description' => __( '(Bool) True if the command was successful, false if not. E.g. array( \'success\' => true )', 'gpte' ) ),
				'msg'     => array( 'short_description' => __( '(String) Further information about the command status.', 'gpte' ) ),
				'data'    => array( 'short_description' => __( '(Array) Further information about the request.', 'gpte' ) ),
			);

			ob_start();
?>
<p>
	<?php echo __( 'Add all of your SQL quieries within this field. To add multiple ones, please separate them using a semicolon:', 'gpte' ); ?>
</p>
<pre>
UPDATE your_table SET your_key = 'Your value';
UPDATE your_table SET another_key = 'Another value';
</pre>
<?php
			$parameter['sql_query']['description'] = ob_get_clean();

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
				'command'            => 'sql_run_query', // required
				'name'              => __( 'Run an SQL query on this website', 'gpte' ),
				'parameter'         => $parameter,
				'returns'           => $returns,
				'returns_code'      => $returns_code,
				'short_description' => __( 'Run one arbitrary SQL queries against the current WordPress database.', 'gpte' ),
				'description'       => array(),
				'integration'       => 'sql',
				'premium'           => true,
			);

		}

		public function execute( $return_args, $data ) {

			if( 
				! defined( 'DB_HOST' )
				|| ! defined( 'DB_USER' )
				|| ! defined( 'DB_PASSWORD' )
				|| ! defined( 'DB_NAME' )
			){
				$return_args['msg']     = __( 'No sufficient details to establish a mySQLi connection.', 'gpte' );
				return $return_args;
			}

			$dbconnect = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
			
			$sql_query = GPTE()->helpers->get_data( $data, 'sql_query' );
			
			if( empty( $sql_query ) ){
				$return_args['msg']     = __( 'You did not set the sql_query argument.', 'gpte' );
				return $return_args;
			}

			$default_return_tags = array(
				'delete',
				'alter',
				'create',
				'replace',
				'truncate',
				'drop',
				'rename',
				'insert',
				'update',
			);

			$result = mysqli_query( $dbconnect, $sql_query );
	
			if( $result ){
				if( preg_match( "/^\s*(" . implode( '|', $default_return_tags ) . ") /i", $sql_query ) ){
					$return_args['success'] = true;
					$return_args['msg'] = __( 'The query has been executed succesfully.', 'gpte' );
					$return_args['data']['affected_rows'] = mysqli_affected_rows( $dbconnect );
				} else {

					$return_args['success'] = true;
					$return_args['msg'] = __( 'The query has been executed successfully.', 'gpte' );

					$first = true;
					$return_args['data']['rows'] = array();
	
					while( $row = mysqli_fetch_assoc( $result ) ) {

						if( $first ) {
							$return_args['data']['table_keys'] = array_keys( $row );
							$first = false;
						}

						$return_args['data']['rows'][] = $row;
					}
				}
			} else {
				$return_args['msg'] = __( 'An error occured while executing the SQL.', 'gpte' );
				$return_args['data']['error'] = mysqli_error( $dbconnect );
			}

			return $return_args;

		}
	}
endif;
