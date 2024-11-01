<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Orders extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Orders', 'wc-reports-lite' ),
            'plural'   => esc_html__( 'Order', 'wc-reports-lite' ),
            'ajax'     => true
        ] );
    }

    /**
     * Get report
     */
	public static function wcrl_getReports() {
		global $wpdb;

		// SELECT
		$wcrl_sqlColumns = "
        wcrl_woocommerce_order_items.order_item_name AS 'product_name'
        ,wcrl_woocommerce_order_items.order_item_id AS order_item_id
        ,wcrl_woocommerce_order_itemmeta2.meta_value AS product_id                             
        ,SUM(woocommerce_order_itemmeta.meta_value) AS 'quantity' 
        ,SUM(wcrl_woocommerce_order_itemmeta4.meta_value) AS 'amount'
        ,wcrl_woocommerce_order_itemmeta5.meta_value AS 'unit_price'
        ,DATE(posts.post_date) AS post_date
        ,posts.ID as post_id
        ";

		// FROM
		$wcrl_sqlJoins = "{$wpdb->prefix}woocommerce_order_items as wcrl_woocommerce_order_items                        
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=wcrl_woocommerce_order_items.order_item_id
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta4 ON wcrl_woocommerce_order_itemmeta4.order_item_id=wcrl_woocommerce_order_items.order_item_id
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta5 ON wcrl_woocommerce_order_itemmeta5.order_item_id=wcrl_woocommerce_order_items.order_item_id
        ";

		$wcrl_sqlJoins .= "
        LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=wcrl_woocommerce_order_items.order_id 
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta2 ON wcrl_woocommerce_order_itemmeta2.order_item_id=wcrl_woocommerce_order_items.order_item_id
        ";

		// WHERE
		$wcrl_sqlCondition = "
        1*1
        AND woocommerce_order_itemmeta.meta_key = '_qty'
        AND wcrl_woocommerce_order_itemmeta4.meta_key = '_line_total'
        AND wcrl_woocommerce_order_itemmeta5.meta_key = '_line_subtotal' 
        ";
		$wcrl_sqlCondition .="   
        AND posts.post_type IN ( 'shop_order','product', 'product_variation' )  
        AND wcrl_woocommerce_order_itemmeta2.meta_key = '_product_id'                  
        ";

		// Search Report By date
		$wcrl_fromDateCondition = '';
		if (isset($_GET['wcrl_searchByDate'])) {
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Today') {
				$wcrl_fromDateCondition = " AND DATE(posts.post_date) = '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Yesterday') {
				$wcrl_fromDateCondition = " AND DATE(posts.post_date) = '".date('Y-m-d',strtotime("-1 days"))."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Week') {
				$wcrl_fromDateCondition = " AND DATE(posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-7 days"))."' AND '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Month') {
				$wcrl_fromDateCondition = " AND DATE(posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-30 days"))."' AND '".date('Y-m-d')."'";
			}
			if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Year') {
				$wcrl_fromDateCondition = " AND DATE(posts.post_date) BETWEEN '".date('Y-m-d',strtotime("-365 days"))."' AND '".date('Y-m-d')."'";
			}
		}

		// Group
		$wcrl_sqlGroupBy = " GROUP BY post_id";

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
	            echo '<a href='.get_edit_post_link($item['post_id']).'>'.esc_html( '#'.$item['post_id'] ).' '.$order->get_billing_first_name().' '.$order->get_billing_last_name().'</a>';
	            break;
            case 'order_date':
                echo '<code>'.date_i18n('Y-m-d', strtotime( $order->get_date_created() ) ).'</code>';
                break;
	        case 'order_status':
		        echo '<mark class="order-status status-'.$order->get_status().'"><span>'.wc_get_order_status_name($order->get_status()).'</span></mark>';
		        break;
	        case 'quantity' :
		        echo $item['quantity'];
		        break;
	        case 'items' :
		        echo '<div class="wcrl_customer_products">';
		        echo '<a class="button" href="#">'.esc_html__('Display Products','wc-reports-lite').'</a>';
		        echo '<div class="wcrl_customer_products_list">';
		        foreach ($order->get_items() as $product ){
			        echo '<p><span class="dashicons dashicons-yes"></span> '.esc_html($product['name']).' ('.$product['quantity'].'&#215;'.wc_price($product['subtotal']).')'.'</p>';
		        }
		        echo '</div>';
		        echo '</div>';
		        break;
	        case 'order_shipping':
		        echo wc_price($order->get_total_shipping());
		        break;
	        case 'order_discount':
                echo wc_price($order->get_total_discount());
		        break;
            case 'amount':
                echo wc_price($order->get_total());
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
            'order_date' => esc_html__( 'Date', 'wc-reports-lite' ),
            'order_status' => esc_html__( 'Status', 'wc-reports-lite' ),
            'quantity'      => esc_html__( 'Quantity', 'wc-reports-lite' ),
            'items'      => esc_html__( 'Products Purchased', 'wc-reports-lite' ),
            'order_shipping'      => esc_html__( 'Shipping', 'wc-reports-lite' ),
            'order_discount'      => esc_html__( 'Discount', 'wc-reports-lite' ),
            'amount'         => esc_html__( 'Total Amount', 'wc-reports-lite' ),
        );
        return $columns;
    }

	/**
	 * Sortable Columns
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'order_number'  => array('post_id',false),
			'quantity'  => array('quantity',false),
			'amount' => array('amount',false),
		);
		return $sortable_columns;
	}
	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'post_id';

		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? sanitize_text_field($_GET['order']) : 'desc';
		// Determine sort order
		$result = strnatcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

	/**
	 * tablenav
	 */
	public function tablenav( $which = 'top' ) {
		?>
        <div class="tablenav themes <?php echo $which; ?>">
            <span class="spinner"></span>
            <br class="clear" />
        </div>
		<?php
	}

	/**
     * Handles data
     */
    public function prepare_items()
    {
	    $per_page = $this->get_items_per_page('order_per_page');
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
     * WCRL_Orders Output
     */
    public function WCRL_output() {
        $this->prepare_items();
        echo '<div class="wrap">';
        echo '<h2>'.esc_html__('Report Based On Orders','wc-reports-lite').'</h2>';
        echo '<div id="poststuff">';
        WCRL_Search_Date::WCRL_searchByDate();
        echo '<form method="get">';
            $this->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

