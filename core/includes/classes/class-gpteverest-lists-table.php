<?php

if( ! defined( 'ABSPATH' ) ){ 
    exit;
}

/**
 * GPTE_WP_List_Table Class
 *
 * This class contains a custom wrapper for the list table
 *
 * @since 1.0.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The WP List table wrapper of the plugin.
 * 
 * Example dataset for the __construt():
 * 
 * array(
 * 	'labels' => array(
 * 		'singular' => __( 'post', 'gpte' ),
 * 		'plural' => __( 'posts', 'gpte' ),
 * 		'search_placeholder' => '',
 * 	),
 * 	'settings' => array(
 * 		'per_page' => 20,
 * 		'default_order_by' => 'id',
 * 		'default_order' => 'DESC',
 * 		'show_search' => true,
 * 		'custom_styles' => '',
 * 	),
 * 	'columns' => array(
 * 		'column_slug' => array(
 * 			'label' => __( 'My Label', 'gpte' ),
 * 			'sortable' => 'ASC',
 * 			'callback' => array( $this, 'my_custom_callback' ),
 * 			'actions_callback' => array( $this, 'my_custom_callback' ),
 * 		)
 * 	),
 *  'bulk_actions' => array(
 * 		'delete' => array(
 * 			'label' => __( 'Delete', 'gpte' ),
 * 			'callback' => array( $this, 'my_custom_callback' ),
 * 		)
 * 	),
 * 	'item_filter' => null
 * )
 *
 * @since 1.0.0
 * @package GPTE
 * @author Jannis Thuemmig
 */
class GPTE_WP_List_Table extends WP_List_Table {

	/**
	 * The list table labels
	 *
	 * @var array
	 */
	private $labels = array();

	/**
	 * The list table columns
	 *
	 * @var array
	 */
	private $columns = array();

	/**
	 * The custom settings
	 *
	 * @var boolean
	 */
	private $settings = false;

	/**
	 * Register all bulk actions
	 *
	 * @var array
	 */
	private $bulk_actions = null;

	/**
	 * The filter used to prepare the items
	 *
	 * @var mixed
	 */
	private $item_filter = null;

	public function __construct( $args = array() ) {

		if( isset( $args['labels'] ) ){
			$this->labels = $args['labels'];
		}

		$singular = '';
		if( isset( $this->labels['singular'] ) ){
			$singular = $args['labels']['singular'];
		}

		$plural = '';
		if( isset( $this->labels['plural'] ) ){
			$plural = $args['labels']['plural'];
		}

		if( isset( $args['columns'] ) ){
			$this->columns = $args['columns'];
		}

		if( isset( $args['settings'] ) && isset( $args['settings'] ) ){
			$this->settings = $args['settings'];
		}

		if( isset( $args['bulk_actions'] ) && ! empty( $args['bulk_actions'] ) ){
			$this->bulk_actions = $args['bulk_actions'];
		}

		if( isset( $args['item_filter'] ) && ! empty( $args['item_filter'] ) ){
			$this->item_filter = $args['item_filter'];
		}
		
		parent::__construct( array(
			'singular' => $singular,
			'plural' => $plural,
			'ajax' => false,
		) );
	}

	public function prepare_items() {

		$per_page = 20;
		if( isset( $this->settings['per_page'] ) && $this->settings['per_page'] ){
			$per_page = intval( $this->settings['per_page'] );
		}

		$order_by = 'id';
		if( isset( $this->settings['default_order_by'] ) && $this->settings['default_order_by'] ){
			$order_by = esc_sql( $this->settings['default_order_by'] );
		}

		$order = 'DESC';
		if( isset( $this->settings['default_order'] ) && $this->settings['default_order'] ){
			$order = esc_sql( $this->settings['default_order'] );
		}

		$paged = $this->get_pagenum();

		$args = array(
			'per_page' => $per_page,
			'paged' => $paged,
			'orderby' => $order_by,
			'order' => $order,
			'offset' => ( $paged - 1 ) * $per_page,
		);	

		if ( 
			! empty( $_REQUEST['s'] ) 
			&& isset( $this->settings['show_search'] )
			&& ! empty( $this->settings['show_search'] )
		){
			$args['s'] = $_REQUEST['s'];
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {

			if( isset( $this->columns[ $_REQUEST['orderby'] ] ) ){
				$args['orderby'] = $_REQUEST['orderby'];
			}
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			if ( 'asc' == strtolower( $_REQUEST['order'] ) ) {
				$args['order'] = 'ASC';
			} elseif ( 'desc' == strtolower( $_REQUEST['order'] ) ) {
				$args['order'] = 'DESC';
			}
		}

		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

		$item_data = array();

		//Process bulk actions before running any kind of filter
		$args = $this->process_bulk_actions( $args );

		if( ! empty( $this->item_filter ) ){
			$item_data = call_user_func( $this->item_filter, $args );
		}

		if( isset( $item_data['items'] ) ){
			$this->items = $item_data['items'];
		}

		$total_items = 0;
		if( isset( $item_data['total'] ) ){
			$total_items = $item_data['total'];
		}

		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page,
		) );
	}

	public function get_columns() {

		$columns = array();

		if( isset( $this->settings['bulk_support'] ) && $this->settings['bulk_support'] ){
			$columns['cb'] = '<input type="checkbox" />';
		}

		foreach( $this->columns as $column_slug => $column_data ){
			$columns[ $column_slug ] = ( isset( $column_data['label'] ) ) ? $column_data['label'] : $column_slug;
		}

		return $columns;
	}

	protected function get_sortable_columns() {

		$columns = array();

		foreach( $this->columns as $column_slug => $column_data ){
			if( isset( $column_data['sortable'] ) && ! empty( $column_data['sortable'] ) ){

				$order = false; //descending by default

				if( $column_data['sortable'] === 'ASC' ){
					$order = true;
				}

				$columns[ $column_slug ] = array( $column_slug, $order );
			}
		}

		return $columns;
	}

	protected function get_bulk_actions() {

		$actions = array();

		if( isset( $this->bulk_actions ) && ! empty( $this->bulk_actions ) ){

			foreach( $this->bulk_actions as $b_action => $b_data ){
				if( isset( $b_data['label'] ) ){
					$actions[ $b_action ] = $b_data['label'];
				}
			}
			
		}

		return $actions;
	}

	/**
	 * Process all of the available bulk actions
	 * 
	 * The argument supports filtering the prepare_items argument
	 * to further customize the query values
	 *
	 * @param array $args
	 * @return array
	 */
	protected function process_bulk_actions( $args ) {

		if( isset( $this->bulk_actions ) && ! empty( $this->bulk_actions ) ){

			foreach( $this->bulk_actions as $b_action => $b_data ){
				if( isset( $b_data['callback'] ) ){
					$args = call_user_func( $b_data['callback'], $args, $b_action );
				}
			}
			
		}

		return $args;
	}

	protected function column_default( $item, $column_name ) {

		$content = '';

		if( isset( $this->columns[ $column_name ] ) && isset( $this->columns[ $column_name ]['callback'] ) ){
			$content = call_user_func( $this->columns[ $column_name ]['callback'], $item, $column_name, $this->columns[ $column_name ] );
		}

		return $content;
	}

	public function column_cb( $item ) {	
		$content = '';

		if( isset( $item->id ) ){
			$content = sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['singular'],
				$item->id
			);
		}

		return $content;
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {

		$actions = array();

		if( isset( $this->columns[ $column_name ] ) && isset( $this->columns[ $column_name ]['actions_callback'] ) ){
			$actions = call_user_func( $this->columns[ $column_name ]['actions_callback'], $item, $column_name, $primary, $this->columns[ $column_name ] );
		}

		return $this->row_actions( $actions );
	}

	/**
	 * Overwrite the default handler for search boxes
	 * for better customization
	 *
	 * @param string $text
	 * @param string $input_id
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
	<input type="search" class="gpte-form-input" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php echo ( isset( $this->labels['search_placeholder'] ) ) ? esc_html( $this->labels['search_placeholder'] ) : ''; ?>" />
		<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
		<?php
	}

	public function display(){

		$styles = '';
		if( isset( $this->settings['custom_styles'] ) && $this->settings['custom_styles'] ){
			$styles = esc_html( $this->settings['custom_styles'] );
		}
?>

<?php if( ! empty( $styles ) ) : ?>
	<style>
		<?php echo $styles; ?>
	</style>
<?php endif; ?>

<div class="gpte-list-table-wrapper">
	<form class="gpte-table-form" method="get" action="">

		<?php if( isset( $_REQUEST['page'] ) ) : ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
		<?php endif; ?>

		<?php if( isset( $_REQUEST['gptevrs'] ) ) : ?>
			<input type="hidden" name="gptevrs" value="<?php echo esc_attr( $_REQUEST['gptevrs'] ); ?>" />
		<?php endif; ?>

		<?php if( isset( $this->settings['show_search'] ) && ! empty( $this->settings['show_search'] ) ) : ?>
			<?php $this->search_box( sprintf( __( 'Search %s', 'gpte' ), $this->labels['plural'] ), 'gpte-form-search' ); ?>
		<?php endif; ?>
		<?php parent::display(); ?>
	</form>
</div>


	<?php
		
	}
}
