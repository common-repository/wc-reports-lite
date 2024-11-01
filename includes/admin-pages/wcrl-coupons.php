<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Coupons extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Coupons', 'wc-reports-lite' ),
            'plural'   => esc_html__( 'Coupon', 'wc-reports-lite' ),
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
        wcrl_woocommerce_order_items.order_item_name AS 'order_item_name',
		wcrl_woocommerce_order_items.order_item_name AS 'coupon_name', 
		SUM(woocommerce_order_itemmeta.meta_value) AS 'amount' , 
		Count(*) AS 'quantity'";
        // Select {posts} and {postmeta} table from database
        $wcrl_sqlJoins = "
		{$wpdb->prefix}woocommerce_order_items as wcrl_woocommerce_order_items 
		LEFT JOIN	{$wpdb->prefix}posts as wcrl_posts ON wcrl_posts.ID = wcrl_woocommerce_order_items.order_id
		LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=wcrl_woocommerce_order_items.order_item_id
		";

        // condition for select sql
        $wcrl_sqlCondition = "
		wcrl_posts.post_type 								=	'shop_order'
		AND wcrl_woocommerce_order_items.order_item_type		=	'coupon' 
		AND woocommerce_order_itemmeta.meta_key			=	'discount_amount'";

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
        $wcrl_sqlGroupBy = " Group BY wcrl_woocommerce_order_items.order_item_name";

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
        switch ( $column_name ) {
            case 'coupon_name':
                echo $item['coupon_name'];
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
            'coupon_name' => esc_html__( 'Coupon Name', 'wc-reports-lite' ),
            'quantity'      => esc_html__( 'Coupons Used In Total', 'wc-reports-lite' ),
            'amount'         => esc_html__( 'Discounts In Total', 'wc-reports-lite' ),
        );
        return $columns;
    }

	/**
	 * Sortable Columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'quantity'  => array('quantity',false),
			'amount' => array('amount',false),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'coupon_name';
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
	    $per_page = $this->get_items_per_page('coupon_per_page');
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
     * WCRL_Coupons Output
     */
    public function WCRL_output() {
        $this->prepare_items();
        echo '<div class="wrap">';
        echo '<h2>'.esc_html__('Report Based On Discount Coupons','wc-reports-lite').'</h2>';
        echo '<div id="poststuff">';
        WCRL_Search_Date::WCRL_searchByDate();
        echo '<form method="get">';
        $this->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}