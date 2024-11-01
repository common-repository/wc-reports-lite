<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Categories extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Categories', 'wc-reports-lite' ),
            'plural'   => esc_html__( 'Category', 'wc-reports-lite' ),
            'ajax'     => false
        ] );
    }

    /**
     * Get report
     */
    public static function wcrl_getReports() {
        global $wpdb;

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

        // Select sql columns
        $wcrl_sqlColumns = " 
         COUNT(wcrl_woocommerce_order_itemmeta_product_qty.meta_value) AS 'orders_placed'
		,SUM(wcrl_woocommerce_order_itemmeta_product_qty.meta_value) AS quantity
		,SUM(wcrl_woocommerce_order_itemmeta_product_line_total.meta_value) AS amount
		,wcrl_terms_product_id.term_id AS category_id
		,wcrl_terms_product_id.name AS category_name
		,wcrl_term_taxonomy_product_id.parent AS parent_category_id
		,wcrl_terms_parent_product_id.name AS parent_category_name";

        // Select {posts} and {postmeta} table from database
        $wcrl_sqlJoins= "{$wpdb->prefix}woocommerce_order_items as wcrl_woocommerce_order_items
		 LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_product_id ON wcrl_woocommerce_order_itemmeta_product_id.order_item_id=wcrl_woocommerce_order_items.order_item_id
		 LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_product_qty ON wcrl_woocommerce_order_itemmeta_product_qty.order_item_id=wcrl_woocommerce_order_items.order_item_id
		 LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_product_line_total ON wcrl_woocommerce_order_itemmeta_product_line_total.order_item_id=wcrl_woocommerce_order_items.order_item_id";

        $wcrl_sqlJoins .= " 
        LEFT JOIN  {$wpdb->prefix}term_relationships as wcrl_term_relationships_product_id ON wcrl_term_relationships_product_id.object_id = wcrl_woocommerce_order_itemmeta_product_id.meta_value 
		LEFT JOIN  {$wpdb->prefix}term_taxonomy as wcrl_term_taxonomy_product_id ON wcrl_term_taxonomy_product_id.term_taxonomy_id = wcrl_term_relationships_product_id.term_taxonomy_id
		LEFT JOIN  {$wpdb->prefix}terms as wcrl_terms_product_id ON wcrl_terms_product_id.term_id =	wcrl_term_taxonomy_product_id.term_id 
		LEFT JOIN  {$wpdb->prefix}terms as wcrl_terms_parent_product_id ON wcrl_terms_parent_product_id.term_id = wcrl_term_taxonomy_product_id.parent
        LEFT JOIN  {$wpdb->prefix}posts as wcrl_posts ON wcrl_posts.id=wcrl_woocommerce_order_items.order_id";

        // condition for select sql
        $wcrl_sqlCondition = " 1*1 
		AND wcrl_woocommerce_order_items.order_item_type 					= 'line_item'
		AND wcrl_woocommerce_order_itemmeta_product_id.meta_key 			= '_product_id'
		AND wcrl_woocommerce_order_itemmeta_product_qty.meta_key 			= '_qty'
		AND wcrl_woocommerce_order_itemmeta_product_line_total.meta_key 	= '_line_total'
		AND wcrl_term_taxonomy_product_id.taxonomy 						= 'product_cat'
		AND wcrl_posts.post_type 											= 'shop_order'";

        // Group
        $wcrl_sqlGroupBy = " GROUP BY category_id";

        // Sort
        $wcrl_sqlOrderBy = " ORDER BY %s DESC";

        // sql query for result
        $wcrl_sql ="
            SELECT $wcrl_sqlColumns 
            FROM   $wcrl_sqlJoins
            WHERE  $wcrl_sqlCondition $wcrl_fromDateCondition $wcrl_sqlGroupBy $wcrl_sqlOrderBy
        ";
        $wcrl_Result = $wpdb->get_results($wpdb->prepare($wcrl_sql,'amount'),'ARRAY_A');
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
        switch ( $column_name ) {
            case 'category_name':
                echo $item['category_name'];
                break;
	        case 'orders_placed':
		        echo $item['orders_placed'];
		        break;
            case 'quantity' :
                echo $item['quantity'];
                break;
            case 'amount':
                echo wc_price($item['amount']);
                break;
        }
        return '';
    }

    /**
     * Get columns.
     */
    public function get_columns() {
        $columns = array(
            'category_name' => esc_html__( 'Category Name', 'wc-reports-lite' ),
            'orders_placed'      => esc_html__( 'Orders Placed', 'wc-reports-lite' ),
            'quantity'      => esc_html__( 'Items Purchased', 'wc-reports-lite' ),
            'amount'         => esc_html__( 'Total Amount', 'wc-reports-lite' ),
        );
        return $columns;
    }

	/**
	 * Sortable Columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'orders_placed'  => array('orders_placed',false),
			'quantity'  => array('quantity',false),
			'amount' => array('amount',false),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'category_name';
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
	    $per_page = $this->get_items_per_page('category_per_page');
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
     * WCRL_Categories Output
     */
    public function WCRL_output() {
        $this->prepare_items();
        echo '<div class="wrap">';
        echo '<h2>'.esc_html__('Report Based On Categories','wc-reports-lite').'</h2>';
        echo '<div id="poststuff">';
        WCRL_Search_Date::WCRL_searchByDate();
        echo '<form method="get">';
        $this->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}