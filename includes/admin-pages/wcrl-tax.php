<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Tax extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Taxes', 'wc-reports-lite' ),
            'plural'   => esc_html__( 'Tax', 'wc-reports-lite' ),
            'ajax'     => false
        ] );
    }

    /**
     * Get report
     */
    public static function wcrl_getReports() {
	    global $wpdb;
	    $wcrl_id_order_status_join = '';

	    // SELECT
	    $wcrl_sqlColumns = "
            SUM(wcrl_woocommerce_order_itemmeta_tax_amount.meta_value)  AS _order_tax,
            SUM(wcrl_woocommerce_order_itemmeta_shipping_tax_amount.meta_value)  AS _shipping_tax_amount,
            SUM(wcrl_postmeta1.meta_value)  AS _order_shipping_amount,
            SUM(wcrl_postmeta2.meta_value)  AS _order_total_amount,
            COUNT(wcrl_posts.ID)  AS _order_count,
            wcrl_woocommerce_order_items.order_item_name as tax_rate_code, 
            wcrl_woocommerce_tax_rates.tax_rate_name as tax_rate_name, 
            wcrl_woocommerce_tax_rates.tax_rate as order_tax_rate, 
            wcrl_woocommerce_order_itemmeta_tax_amount.meta_value AS order_tax,
            wcrl_woocommerce_order_itemmeta_shipping_tax_amount.meta_value AS shipping_tax_amount,
            wcrl_postmeta1.meta_value as order_shipping_amount,
            wcrl_postmeta2.meta_value as order_total_amount,
            wcrl_postmeta3.meta_value as billing_state,
            wcrl_postmeta4.meta_value as billing_country
		";

	    $wcrl_sqlColumns .=", CONCAT(wcrl_woocommerce_order_items.order_item_name,'-',wcrl_woocommerce_tax_rates.tax_rate_name,'-',wcrl_woocommerce_tax_rates.tax_rate,'-',wcrl_postmeta4.meta_value,'',wcrl_postmeta3.meta_value) as group_column";

	    // FROM
	    $wcrl_sqlJoins = "{$wpdb->prefix}woocommerce_order_items as wcrl_woocommerce_order_items";
	    $wcrl_sqlJoins .= "$wcrl_id_order_status_join LEFT JOIN  {$wpdb->prefix}postmeta as wcrl_postmeta1 ON wcrl_postmeta1.post_id=wcrl_woocommerce_order_items.order_id
	    LEFT JOIN  {$wpdb->prefix}postmeta as wcrl_postmeta2 ON wcrl_postmeta2.post_id=wcrl_woocommerce_order_items.order_id
		LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_tax ON wcrl_woocommerce_order_itemmeta_tax.order_item_id=wcrl_woocommerce_order_items.order_item_id
		LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_tax_amount ON wcrl_woocommerce_order_itemmeta_tax_amount.order_item_id=wcrl_woocommerce_order_items.order_item_id
		LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta_shipping_tax_amount ON wcrl_woocommerce_order_itemmeta_shipping_tax_amount.order_item_id=wcrl_woocommerce_order_items.order_item_id
		LEFT JOIN  {$wpdb->prefix}woocommerce_tax_rates as wcrl_woocommerce_tax_rates ON wcrl_woocommerce_tax_rates.tax_rate_id=wcrl_woocommerce_order_itemmeta_tax.meta_value
		LEFT JOIN  {$wpdb->prefix}posts as wcrl_posts ON wcrl_posts.ID=	wcrl_woocommerce_order_items.order_id
		LEFT JOIN  {$wpdb->prefix}postmeta as wcrl_postmeta3 ON wcrl_postmeta3.post_id=wcrl_woocommerce_order_items.order_id
		LEFT JOIN  {$wpdb->prefix}postmeta as wcrl_postmeta4 ON wcrl_postmeta4.post_id=wcrl_woocommerce_order_items.order_id";
	    // WHERE
	    $wcrl_sqlCondition = "wcrl_postmeta1.meta_key = '_order_shipping' AND wcrl_woocommerce_order_items.order_item_type = 'tax'
		AND wcrl_posts.post_type='shop_order' 
		AND wcrl_postmeta2.meta_key='_order_total'
		AND wcrl_woocommerce_order_itemmeta_tax.meta_key='rate_id'
		AND wcrl_woocommerce_order_itemmeta_tax_amount.meta_key='tax_amount'
		AND wcrl_woocommerce_order_itemmeta_shipping_tax_amount.meta_key='shipping_tax_amount'
		AND wcrl_postmeta3.meta_key='_billing_state'
		AND wcrl_postmeta4.meta_key='_billing_country'";


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
	    $wcrl_sqlGroupBy = " group by %s";

	    // Sort
	    $wcrl_sqlOrderBy = " ORDER BY (wcrl_woocommerce_tax_rates.tax_rate + 0)  ASC";

	    // sql query for result
	    $wcrl_sql ="
            SELECT $wcrl_sqlColumns 
            FROM   $wcrl_sqlJoins
            WHERE  $wcrl_sqlCondition $wcrl_fromDateCondition $wcrl_sqlGroupBy $wcrl_sqlOrderBy 
        ";
	   
        $wcrl_Result = $wpdb->get_results($wpdb->prepare($wcrl_sql, 'group_column'),'ARRAY_A');
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
            case 'tax_rate_name':
                echo $item['tax_rate_name'];
                break;
            case 'tax_rate' :
                echo $item['order_tax_rate'].'%';
                break;
            case 'tax_quantity_orders':
                echo $item['_order_count'];
                break;
	        case 'tax_amount':
		        echo wc_price($item['_order_tax']);
		        break;
	        case 'order_amount':
		        echo wc_price($item['_order_total_amount']);
		        break;
            case 'shipping_amount':
                echo wc_price($item['_order_shipping_amount']);
                break;
            case 'tax_shipping_amount':
	            echo wc_price($item['_shipping_tax_amount']);
                break;
	        case 'tax_total':
		        echo wc_price($item['_shipping_tax_amount']+$item['_order_tax']);
		        break;
        }
        return '';
    }

    /**
     * Get columns.
     */
    public function get_columns() {
        $columns = array(
            'tax_rate_name' => esc_html__( 'Tax Name', 'wc-reports-lite' ),
            'tax_rate'      => esc_html__( 'Tax Rate', 'wc-reports-lite' ),
            'tax_quantity_orders'         => esc_html__( 'Quantity Orders', 'wc-reports-lite' ),
            'order_amount'         => esc_html__( 'Order Amount', 'wc-reports-lite' ),
            'shipping_amount'         => esc_html__( 'Shipping Amount', 'wc-reports-lite' ),
            'tax_amount'         => esc_html__( 'Order Tax', 'wc-reports-lite' ),
            'tax_shipping_amount'         => esc_html__( 'Shipping Tax', 'wc-reports-lite' ),
            'tax_total'         => esc_html__( 'Total Tax', 'wc-reports-lite' ),
        );
        return $columns;
    }

	/**
	 * Sortable Columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'tax_quantity_orders'  => array('_order_count',false),
			'tax_amount'  => array('_order_tax',false),
			'order_amount'  => array('_order_total_amount',false),
			'shipping_amount'  => array('_order_shipping_amount',false),
			'tax_shipping_amount' => array('_shipping_tax_amount',false),
			'tax_total'  => array('_shipping_tax_amount',false),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'group_column';
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
	    $per_page = $this->get_items_per_page('tax_per_page');
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
     * WCRL_Products Output
     */
    public function wcrl_output() {
        $this->prepare_items();
        echo '<div class="wrap">';
        echo '<h2>'.esc_html__('Report Based On Taxes','wc-reports-lite').'</h2>';
        echo '<div id="poststuff">';
        WCRL_Search_Date::WCRL_searchByDate();
        echo '<form method="get">';
        $this->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}