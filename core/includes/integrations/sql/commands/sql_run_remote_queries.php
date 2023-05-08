<?php
if ( ! class_exists( 'GPTE_Integrations_sql_Actions_sql_run_remote_queries' ) ) :
	/**
	 * Load the sql_run_remote_queries action
	 *
	 * @since 1.0.0
	 * @author Jannis Thuemmig
	 */
	class GPTE_Integrations_sql_Actions_sql_run_remote_queries {

		public function get_details() {

			$sql_tags = GPTE()->sql->get_tags();

			$parameter         = array(
				'db_host' => array(
					'required'          => true,
					'label'             => __( 'Database host', 'gpte' ),
					'short_description' => __( '(String) The database host. E.g. localhost', 'gpte' ),
				),
				'db_user' => array(
					'required'          => true,
					'label'             => __( 'Database user', 'gpte' ),
					'short_description' => __( '(String) The username of the database. E.g. root', 'gpte' ),
				),
				'db_password' => array(
					'required'          => false,
					'label'             => __( 'Database password', 'gpte' ),
					'short_description' => __( '(String) The password for the user of the database.', 'gpte' ),
				),
				'db_name' => array(
					'required'          => true,
					'label'             => __( 'Database name', 'gpte' ),
					'short_description' => __( '(String) The name of the database.', 'gpte' ),
				),
				'queries' => array(
					'required'          => true,
					'label'             => __( 'SQL Queries', 'gpte' ),
					'short_description' => __( '(String) The SQL queries you would like to run.', 'gpte' ),
				),
				'run_multiple_queries'	=> array( 
					'type' => 'select', 
					'default_value' => 'no', 
					'label' => __( 'Run multiple queries', 'gpte' ), 
					'choices' => array( 
						'yes' => __( 'Yes', 'gpte' ),
						'no' => __( 'No', 'gpte' ),
					), 
					'short_description' => __( 'Set this to yes to run multiple SQL queries within the queries argument.', 'gpte' ) 
				),
			);

			$returns           = array(
				'success' => array( 'short_description' => __( '(Bool) True if the action was successful, false if not. E.g. array( \'success\' => true )', 'gpte' ) ),
				'msg'     => array( 'short_description' => __( '(String) Further information about the action status.', 'gpte' ) ),
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
<p>
<?php echo __( 'We also support a variety of predefined tags that you can use within your SQL statements. Simply copy the tag (including the curly brackets) from below and add it to your SQL statements.', 'gpte' ); ?>
</p>

<?php if( ! empty( $sql_tags ) ) : ?>
<ul>
	<?php foreach( $sql_tags as $tag => $tag_data ) : ?>
		<li>
			<p>
				<strong><?php echo esc_html( $tag_data['label'] ); ?></strong>
				<br>
				<small><?php echo esc_html( $tag_data['short_description'] ); ?></small>
				<br>
				<small><strong><?php echo __( 'Example value', 'gpte' ); ?></strong>: <code><?php echo esc_html( $tag_data['value'] ); ?></code></small>
				<br>
				<small><strong><?php echo __( 'Tag', 'gpte' ); ?></strong>: <code>{<?php echo esc_html( $tag ); ?>}</code></small>
			</p>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php
			$parameter['queries']['description'] = ob_get_clean();	

			ob_start();
?>
<p><?php echo __( 'If you would like to run multiple queries within the same request, you must set this argument to "yes".', 'gpte' ); ?></p>
<p><?php echo __( 'Please note that multiple requests do not return any data from a select statement. The statement will be executed, but the response does not contain any other data than the number of affected rows. If you would like to return data using a select statement, use multiple actions with single statements.', 'gpte' ); ?></p>
<?php
			$parameter['run_multiple_queries']['description'] = ob_get_clean();	

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
				  'queries' => 'select * from wp_options limit 2;',
				),
			);

			return array(
				'action'            => 'sql_run_remote_queries', // required
				'name'              => __( 'Run remote SQL queries', 'gpte' ),
				'sentence'          => __( 'run one or multiple remote SQL queries', 'gpte' ),
				'parameter'         => $parameter,
				'returns'           => $returns,
				'returns_code'      => $returns_code,
				'short_description' => __( 'Run one or multiple, arbitrary SQL queries against a given local or remote database.', 'gpte' ),
				'description'       => array(),
				'integration'       => 'sql',
				'premium'           => true,
			);

		}

		public function execute( $return_data, $data ) {

			$return_args = array(
				'success' => false,
				'msg'     => '',
				'data'    => array(),
			);
			$db_host = GPTE()->helpers->get_data( $data, 'db_host' );
			$db_user = GPTE()->helpers->get_data( $data, 'db_user' );
			$db_password = GPTE()->helpers->get_data( $data, 'db_password' );
			$db_name = GPTE()->helpers->get_data( $data, 'db_name' );
			$queries = GPTE()->helpers->get_data( $data, 'queries' );
			$run_multiple_queries = ( GPTE()->helpers->get_data( $data, 'run_multiple_queries' ) === 'yes' ) ? true : false;

			$dbconnect = mysqli_connect( $db_host, $db_user, $db_password, $db_name );

			if( 
				empty( $db_host )
				|| empty( $db_user )
				|| empty( $db_name )
			){
				$return_args['msg']     = __( 'We do not have sufficient details to establish a mySQLi connection.', 'gpte' );
				return $return_args;
			}

			if( empty( $dbconnect ) ){
				$return_args['msg']     = __( 'we could not connect to the given database.', 'gpte' );
				return $return_args;
			}

			if( empty( $queries ) ){
				$return_args['msg']     = __( 'Please set the queries argument.', 'gpte' );
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

			//Maybe replace tags
			$queries = GPTE()->sql->replace_tags( $queries );

			if( $run_multiple_queries ){
				$result = mysqli_multi_query( $dbconnect, $queries );
			} else {
				$result = mysqli_query( $dbconnect, $queries );
			}

			if( $result ){
				if( 
					$run_multiple_queries
					|| preg_match( "/^\s*(" . implode( '|', $default_return_tags ) . ") /i", $queries )
				){
					$return_args['success'] = true;

					if( $run_multiple_queries ){
						$return_args['msg'] = __( 'The mysql queries have been executed successfully.', 'gpte' );
					} else {
						$return_args['msg'] = __( 'The query has been executed succesfully.', 'gpte' );
					}
					
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

			$return_args['data']['queries'] = $queries;

			return $return_args;

		}
	}
endif;
