<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCRL_Products extends WP_List_Table {
	var $stored_variations	= array();
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Products', 'wc-reports-lite' ),
            'plural'   => esc_html__( 'Product', 'wc-reports-lite' ),
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
        ,COUNT(woocommerce_order_itemmeta.meta_value) AS 'orders_placed'                             
        ,SUM(woocommerce_order_itemmeta.meta_value) AS 'quantity' 
        ,SUM(wcrl_woocommerce_order_itemmeta4.meta_value) AS 'amount'
        ,DATE(posts.post_date) AS post_date
        ,posts.ID as post_id
        ,wcrl_variation_id.meta_value as variation_id
        ";

		// FROM
		$wcrl_sqlJoins = "{$wpdb->prefix}woocommerce_order_items as wcrl_woocommerce_order_items                        
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=wcrl_woocommerce_order_items.order_item_id
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta4 ON wcrl_woocommerce_order_itemmeta4.order_item_id=wcrl_woocommerce_order_items.order_item_id
        ";

		$wcrl_sqlJoins .= "
        LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=wcrl_woocommerce_order_items.order_id 
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_woocommerce_order_itemmeta2 ON wcrl_woocommerce_order_itemmeta2.order_item_id=wcrl_woocommerce_order_items.order_item_id
        LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as wcrl_variation_id ON wcrl_variation_id.order_item_id=wcrl_woocommerce_order_items.order_item_id
        ";

		// WHERE
		$wcrl_sqlCondition = "
        1*1
        AND woocommerce_order_itemmeta.meta_key = '_qty'
        AND wcrl_woocommerce_order_itemmeta4.meta_key = '_line_total'
        ";
		$wcrl_sqlCondition .="   
        AND posts.post_type IN ( 'shop_order','product', 'product_variation' )  
        AND wcrl_woocommerce_order_itemmeta2.meta_key = '_product_id'
        
        AND wcrl_variation_id.meta_key='_variation_id'                     
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
		$wcrl_sqlGroupBy = " GROUP BY wcrl_woocommerce_order_items.order_item_name,wcrl_variation_id.meta_value";

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
            case 'product_name':
	            $product = new WC_Product_Variable( $item['product_id'] );
	            echo '<a href='.get_edit_post_link($item['product_id']).'>'.esc_html( $product->get_title() ).'</a>';
	            $variations = $product->get_available_variations();
	            $var_data = [];
	            foreach ($variations as $variation) {
		            if($variation['variation_id'] == $item['variation_id']){
			            $var_data[] = $variation['attributes'];
		            }
	            }
	            if($var_data){
		            foreach($var_data[0] as $attrName => $var_name) {
			            $att = str_replace('attribute_','',$attrName);
			            $att_name = ucwords(str_replace('-', ' ', $att));
			            ?>
                        <p><strong><?php echo esc_html($att_name);?>: </strong><?php echo wc_get_order_item_meta($item['order_item_id'],$att);?></p>
			            <?php
		            }
	            }
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
            'product_name' => esc_html__( 'Product Name', 'wc-reports-lite' ),
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
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field($_GET['orderby']) : 'product_name';

		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? sanitize_text_field($_GET['order']) : 'asc';
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
	    global $wpdb;
	    $per_page = $this->get_items_per_page('product_per_page');
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
        echo '<h2>'.esc_html__('Report Based On Products','wc-reports-lite').'</h2>';
        echo '<div id="poststuff">';
        WCRL_Search_Date::WCRL_searchByDate();
        echo '<form method="get">';
            $this->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

