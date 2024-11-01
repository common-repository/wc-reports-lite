<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Report_Stock' ) ) {
	include_once(WP_PLUGIN_DIR.'/woocommerce/includes/admin/reports/class-wc-report-stock.php');
}

class WCRL_Stock extends WC_Report_Stock {
    /**
     * No items found text.
     */
    public function no_items() {
        esc_html_e( 'No out of stock products found.', 'wc-reports-lite' );
    }

    /**
     * Get Products matching stock criteria.
     *
     * @param int $current_page
     * @param int $per_page
     */
    public function get_items( $current_page, $per_page ) {
        global $wpdb;

        $this->max_items = 0;
        $this->items     = array();

        if($_GET["tab"] == "most_stocked"){
            $stock = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 0 ) );
            $query_from = "FROM {$wpdb->posts} as posts
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$stock}'
		";
            $query_from = apply_filters( 'woocommerce_report_most_stocked_query_from', $query_from );
        }else{
            $stock = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
            $query_from = apply_filters(
                'woocommerce_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND postmeta2.meta_key = '_manage_stock' 
			AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
		"
            );
        }
        $this->items     = $wpdb->get_results( $wpdb->prepare( "SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY CAST(postmeta.meta_value AS SIGNED) DESC LIMIT %d, %d;", ( $current_page - 1 ) * $per_page, $per_page ) );
        $this->max_items = $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" );

    }

    /**
     * Output the report.
     */
    public function output_report() {
        $active_tab = "most_stocked";
        if(isset($_GET["tab"]))
        {
            if(sanitize_text_field($_GET["tab"]) == "most_stocked")
            {
                $active_tab = "most_stocked";
            }
            else
            {
                $active_tab = "out_of_stock";
            }
        }else{
            $_GET["tab"] = $active_tab = "most_stocked";
        }
        $this->prepare_items();
        echo '<div class="wrap">';
        ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=wcrl-stock&tab=most_stocked" class="nav-tab <?php if($active_tab == 'most_stocked'){echo 'nav-tab-active';} ?> "><?php esc_html_e('most stocked', 'wc-reports-lite'); ?></a>
                <a href="?page=wcrl-stock&tab=out_of_stock" class="nav-tab <?php if($active_tab == 'out_of_stock'){echo 'nav-tab-active';} ?>"><?php esc_html_e('out of stock', 'wc-reports-lite'); ?></a>
            </h2>
        <?php
        echo '<div id="poststuff" class="woocommerce-reports-wide">';
        $this->display();
        echo '</div>';
        echo '</div>';
    }
}
