<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Shippings extends WP_List_Table {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( [
			'singular' => esc_html__( 'Shippings', 'wc-reports-lite' ),
			'plural'   => esc_html__( 'Shipping', 'wc-reports-lite' ),
			'ajax'     => false
		] );
	}

	/**
	 * Get report
	 */
	public static function wcrl_getReports() {
		global $wpdb;

		// Select sql columns
		$wcrl_sqlColumns = "
		woocommerce_order_items.order_item_type AS 'shipping_type'
		,woocommerce_order_items.order_item_name AS 'shipping_method_title'
        ,woocommerce_order_items.order_item_id AS 'order_item_id'
        ,woocommerce_order_itemmeta.meta_key AS 'cost'
        ,DATE(wcrl_posts.post_date) AS post_date
        ,wcrl_posts.ID as post_id
		";

        //FROM
		$wcrl_sqlJoins = " 
		    {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items                        
            LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
		    LEFT JOIN  {$wpdb->prefix}posts as wcrl_posts ON wcrl_posts.ID=woocommerce_order_items.order_id 
		";

		// WHERE
		$wcrl_sqlCondition = "woocommerce_order_items.order_item_type = 'shipping'
		AND woocommerce_order_itemmeta.meta_key = 'cost'
		";

		// Search Report By date
		$wcrl_fromDateCondition = '';
		if (isset($_GET['wcrl_searchByDate'])) {
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Today') {
				$wcrl_fromDateCondition = " AND DATE(wcrl_posts.post_date) = '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Yesterday') {
				$wcrl_fromDateCondition = " AND DATE(wcrl_posts.post_date) = '".date('Y-m-d',strtotime("-1 days"))."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Week') {
				$wcrl_fromDateCondition = " AND DATE(wcrl_posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-7 days"))."' AND '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Month') {
				$wcrl_fromDateCondition = " AND DATE(wcrl_posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-30 days"))."' AND '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Year') {
				$wcrl_fromDateCondition = " AND DATE(wcrl_posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-365 days"))."' AND '".date('Y-m-d')."'";
			}
		}

		// Group
		$wcrl_sqlGroupBy = " GROUP BY order_item_id";

		// Sort
		$wcrl_sqlOrderBy = " ORDER BY %s DESC";

		// sql query for result
		$wcrl_sql ="
            SELECT $wcrl_sqlColumns 
            FROM   $wcrl_sqlJoins
            WHERE  $wcrl_sqlCondition $wcrl_fromDateCondition $wcrl_sqlGroupBy $wcrl_sqlOrderBy 
        ";

		$wcrl_Result = $wpdb->get_results($wpdb->prepare($wcrl_sql, 'amount'),'ARRAY_A');
		return $wcrl_Result;
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No orders found.', 'wc-reports-lite' );
	}

	/**
	 * Get column value.
	 */
	public function column_default( $item, $column_name ) {
		$order = new WC_Order($item['post_id']);
		switch ( $column_name ) {
			case 'order_number':
				echo '<a href='.get_edit_post_link($item['post_id']).'>'.esc_html( '#'.$item['post_id'] ).'</a>';
				break;
			case 'shipping_method_title':
				echo $item['shipping_method_title'];
				break;
			case 'order_date':
				echo '<code>'.date_i18n('Y-m-d', strtotime( $order->get_date_created() ) ).'</code>';
				break;
			case 'amount':
				echo wc_price($order->get_total_shipping());
				break;
		}
		return '';
	}

	/**
	 * Get columns.
	 */
	public function get_columns() {
		$columns = array(
			'order_number' => esc_html__( 'Order Number', 'wc-reports-lite' ),
			'shipping_method_title' => esc_html__( 'Shipping Methods', 'wc-reports-lite' ),
			'order_date' => esc_html__( 'Date', 'wc-reports-lite' ),
			'amount'         => esc_html__( 'Total', 'wc-reports-lite' ),
		);
		return $columns;
	}

	/**
	 * Sortable Columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'amount' => array('amount',false),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'shipping_method_title';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? sanitize_text_field($_GET['order']) : 'asc';
		// Determine sort order
		$result = strnatcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

	/**
	 * Handles data
	 */
	public function prepare_items()
	{
		global $wpdb;
		$per_page = $this->get_items_per_page('shipping_per_page');
		$this->_column_headers =$this->get_column_info();
		$data = self::wcrl_getReports();
		$this->get_sortable_columns();

		$current_page = $this->get_pagenum();
		$total_items = count( $data );
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
		usort( $data, array( $this, 'usort_reorder' ) );
		$this->items = $data;
	}


	/**
	 * wcrl_Shipping Output
	 */
	public function wcrl_output() {
		$this->prepare_items();
		echo '<div class="wrap">';
		echo '<h2>'.esc_html__('Report Based On Shippings','wc-reports-lite').'</h2>';
		echo '<div id="poststuff">';
		WCRL_Search_Date::WCRL_searchByDate();
		echo '<form method="get">';
		$this->display();
		echo '</form>';
		echo '</div>';
		echo '</div>';
	}
}