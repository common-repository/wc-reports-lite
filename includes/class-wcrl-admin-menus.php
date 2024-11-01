<?php
defined( 'ABSPATH' ) || exit;
include_once WCRL_ADMIN_PAGES.'wcrl-overview.php';
include_once WCRL_ADMIN_PAGES.'wcrl-orders.php';
include_once WCRL_ADMIN_PAGES.'wcrl-products.php';
include_once WCRL_ADMIN_PAGES.'wcrl-categories.php';
include_once WCRL_ADMIN_PAGES.'wcrl-coupons.php';
include_once WCRL_ADMIN_PAGES.'wcrl-payment-gateway.php';
include_once WCRL_ADMIN_PAGES.'wcrl-orders-status.php';
include_once WCRL_ADMIN_PAGES.'wcrl-locations.php';
include_once WCRL_ADMIN_PAGES.'wcrl-shippings.php';
include_once WCRL_ADMIN_PAGES.'wcrl-tax.php';
include_once WCRL_ADMIN_PAGES.'wcrl-stock.php';
include_once WCRL_ADMIN_PAGES.'wcrl-settings.php';
if ( ! class_exists( 'WCRL_Admin_Menus', false ) ) :
    class WCRL_Admin_Menus{
        public function __construct() {
            add_action('admin_menu',array($this,'WCRLSetupMenu'));
	        add_filter('set-screen-option', array ( $this, 'WCRL_set_screen_option' ), 10, 3);
        }
        /**
         * Setup Admin Menus
         */
        public function WCRLSetupMenu(){
            global $submenu;
            if(current_user_can('manage_woocommerce')){
	            $overview_page = add_menu_page(esc_html__('WCRL','wc-reports-lite'),esc_html__('WCRL','wc-reports-lite'),'manage_woocommerce','wcrl-overview',array($this,'WCRL_Overview'),'dashicons-chart-area');
	            $order_page  = add_submenu_page('wcrl-overview',esc_html__('Orders','wc-reports-lite'),esc_html__('Orders','wc-reports-lite'),'manage_woocommerce','wcrl-orders',array($this,'WCRL_Orders'));
	            $product_page  = add_submenu_page('wcrl-overview',esc_html__('Products','wc-reports-lite'),esc_html__('Products','wc-reports-lite'),'manage_woocommerce','wcrl-products',array($this,'WCRL_Products'));
	            $category_page = add_submenu_page('wcrl-overview',esc_html__('Categories','wc-reports-lite'),esc_html__('Categories','wc-reports-lite'),'manage_woocommerce','wcrl-categories',array($this,'WCRL_Categories'));
	            $coupon_page   = add_submenu_page('wcrl-overview',esc_html__('Coupons','wc-reports-lite'),esc_html__('Coupons','wc-reports-lite'),'manage_woocommerce','wcrl-coupons',array($this,'WCRL_Coupons'));
	            $payment_gateway_page   = add_submenu_page('wcrl-overview',esc_html__('Payment Gateway','wc-reports-lite'),esc_html__('Payment Gateway','wc-reports-lite'),'manage_woocommerce','wcrl-payment-gateway',array($this,'WCRL_PaymentGateway'));
	            $order_status_page = add_submenu_page('wcrl-overview',esc_html__('Orders Status','wc-reports-lite'),esc_html__('Orders Status','wc-reports-lite'),'manage_woocommerce','wcrl-orders-status',array($this,'WCRL_OrdersStatus'));
	            $location_page = add_submenu_page('wcrl-overview',esc_html__('Locations','wc-reports-lite'),esc_html__('Locations','wc-reports-lite'),'manage_woocommerce','wcrl-locations',array($this,'WCRL_Locations'));
	            $shipping_page = add_submenu_page('wcrl-overview',esc_html__('Shippings','wc-reports-lite'),esc_html__('Shippings','wc-reports-lite'),'manage_woocommerce','wcrl-shippings',array($this,'WCRL_Shippings'));
	            $tax_page = add_submenu_page('wcrl-overview',esc_html__('Tax','wc-reports-lite'),esc_html__('Tax','wc-reports-lite'),'manage_woocommerce','wcrl-tax',array($this,'WCRL_Tax'));
	            add_submenu_page('wcrl-overview',esc_html__('Stock','wc-reports-lite'),esc_html__('Stock','wc-reports-lite'),'manage_woocommerce','wcrl-stock',array($this,'WCRL_Stock'));
	            add_submenu_page('wcrl-overview',esc_html__('Settings','wc-reports-lite'),esc_html__('Settings','wc-reports-lite'),'manage_woocommerce','wcrl-settings',array($this,'WCRL_Settings'));
	            add_submenu_page('wcrl-overview',esc_html__('Upgrade to WooReports Pro!','wc-reports-lite'),'<span class="brownColor">' . esc_html__('PRO Features','wc-reports-lite') . '</span>','manage_woocommerce','wcrl-pro',array($this,'WCRL_Pro'));
	            $submenu['wcrl-overview'][0][0] = esc_html__('Overview','wc-reports-lite');


	            //Set Screen Options For Pages
	            add_action("load-$overview_page",array($this,'WCRL_screen_option_overview'));
	            add_action("load-$order_page",array($this,'WCRL_screen_option_order'));
	            add_action("load-$product_page",array($this,'WCRL_screen_option_product'));
	            add_action("load-$category_page",array($this,'WCRL_screen_option_category'));
	            add_action("load-$coupon_page",array($this,'WCRL_screen_option_coupon'));
	            add_action("load-$payment_gateway_page",array($this,'WCRL_screen_option_payment_gateway'));
	            add_action("load-$order_status_page",array($this,'WCRL_screen_option_order_status'));
	            add_action("load-$location_page",array($this,'WCRL_screen_option_location'));
	            add_action("load-$shipping_page",array($this,'WCRL_screen_option_shipping'));
	            add_action("load-$tax_page",array($this,'WCRL_screen_option_tax'));
            }
        }

	    /**
	     * Set screen options
	     *
	     * @param $status
	     * @param $option
	     * @param $value
	     *
	     * @return mixed
	     */
	    public function WCRL_set_screen_option( $status, $option, $value ) {
		    switch ($option){
			    case 'order_per_page':
			    	update_user_meta(get_current_user_id(),'order_per_page',$value);
				    //return $value;
				    break;
			    case 'product_per_page':
				    update_user_meta(get_current_user_id(),'product_per_page',$value);
				    break;
			    case 'category_per_page':
				    update_user_meta(get_current_user_id(),'category_per_page',$value);
				    break;
			    case 'coupon_per_page':
				    update_user_meta(get_current_user_id(),'coupon_per_page',$value);
				    break;
			    case 'payment_gateway_per_page':
				    update_user_meta(get_current_user_id(),'payment_gateway_per_page',$value);
				    break;
			    case 'order_status_per_page':
				    update_user_meta(get_current_user_id(),'order_status_per_page',$value);
				    break;
			    case 'location_per_page':
				    update_user_meta(get_current_user_id(),'location_per_page',$value);
				    break;
			    case 'shipping_per_page':
				    update_user_meta(get_current_user_id(),'shipping_per_page',$value);
				    break;
			    case 'tax_per_page':
				    update_user_meta(get_current_user_id(),'tax_per_page',$value);
				    break;
		    }
		    return $status;
	    }

        /**
         * Setup Admin Pages
         */
        public function WCRL_Overview(){
	        $WCRL_Overview = new WCRL_Overview();
	        $WCRL_Overview->WCRL_output();
        }
	    public function WCRL_Orders(){
		    $WCRL_Products = new WCRL_Orders();
		    $WCRL_Products->WCRL_output();
	    }
        public function WCRL_Products(){
	        $WCRL_Products = new WCRL_Products();
	        $WCRL_Products->WCRL_output();
        }
        public function WCRL_Categories(){
	        $WCRL_Categories = new WCRL_Categories();
	        $WCRL_Categories->WCRL_output();
        }
        public function WCRL_Coupons(){
	        $WCRL_Coupons = new WCRL_Coupons();
	        $WCRL_Coupons->WCRL_output();
        }
        public function WCRL_PaymentGateway(){
	        $WCRL_PaymentGateway = new WCRL_PaymentGateway();
	        $WCRL_PaymentGateway->WCRL_output();
        }
        public function WCRL_OrdersStatus(){
	        $WCRL_OrdersStatus = new WCRL_OrdersStatus();
	        $WCRL_OrdersStatus->WCRL_output();
        }
        public function WCRL_Locations(){
	        $WCRL_Locations = new WCRL_Locations();
	        $WCRL_Locations->WCRL_output();
        }
	    public function WCRL_Shippings(){
		    $WCRL_Locations = new WCRL_Shippings();
		    $WCRL_Locations->WCRL_output();
	    }
	    public function WCRL_Tax(){
		    $WCRL_Tax = new WCRL_Tax();
		    $WCRL_Tax->WCRL_output();
	    }
        public function WCRL_Stock(){
	        $WCRL_Stock = new WCRL_Stock();
	        $WCRL_Stock->output_report();
        }
        public function WCRL_Settings(){
	        $WCRL_settings = new WCRL_Settings();
	        $WCRL_settings->WCRL_output();
        }
	    public function WCRL_Pro(){
		    $nurl = get_locale() === WCRL_FARSILANG ? 'https://nikanwp.ir/product/woocommerce-reporting/' : 'https://nikanwp.com/woocommerce-reporting/#pricing';
		    ?>
		    <div class="wrap">
                <div class="woo_pro_head">
                    <span class="wooreports_logo"></span>
                    <div class="woo_pro_content">
                        <h1 class="wooreport_text"><?php echo esc_html__('WooReports Pro','wc-reports-lite');?></h1>
                        <p><?php echo esc_html__('Great features with the pro version of the WooReports plugin','wc-reports-lite');?></p>
                        <a href="<?php echo $nurl;?>" class="button button-primary"><?php echo esc_html__('Upgrade to pro version','wc-reports-lite');?></a>
                    </div>
                    <div class="clear"></div>
                </div>
                <hr>
                <div class="woo_pro_features">
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/profit.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Profit Of Sales Report','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('You can enter the cost of each product at the same time as the price of the product, so that your profit and loss reporting system displays your store. also, you can set cost for products simple and variable.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/export.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Export Reports','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('In all type of the reports, it is possible to export reports received in PDF, CSV, Excel, Print formats.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/search_filter.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Advance Search Filter','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('There is a separate search report for each page. For example, you can filter your sales reports for a specific product.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/email.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Email Scheduler','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('In the plugin settings you can set a summary of your sales reports in a timed manner.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/visual_report.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Visual Reports','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('In all type of the reports, you can view the reports in both the bar chart and line chart.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/dokan.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Dokan Integration','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('If you use the Dokan plugin, you can view sales reports from your sellers.','wc-reports-lite');?></p>
                    </div>
                    <div class="wcrl_col">
                        <img src="<?php echo plugins_url('assets/images/customer.jpg',dirname(__FILE__));?>">
                        <h2><?php echo esc_html__('Customer Reports','wc-reports-lite');?></h2>
                        <p><?php echo esc_html__('You may need to see all the orders and products of a particular customer in a certain period of time and access their information, so customer reporting with an advanced search filter will help you to Get reports.','wc-reports-lite');?></p>
                    </div>
                </div>
		    </div>
			<?php
	    }

	    /**
	     * Setup Screen Options
	     */
	    public function WCRL_per_page(){
	    	$get_screen_id = get_current_screen();
	    	$report_per_page = $get_screen_id->id.'_per_page';
	    }
		// Overview page
	    public function WCRL_screen_option_overview(){
		    $WCRL_overview_boxes = new WCRL_Overview();
		    $WCRL_overview_boxes->WCRL_basicAddMetaBox();
	    }
	    // Order page
	    public function WCRL_screen_option_order(){
	    	global $WCRL_order_screen,$per_page;

		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'order_per_page',
		    );
		    add_screen_option( $option, $args );
		    $WCRL_order_screen = new WCRL_Orders();
	    }
	    // Product page
	    public function WCRL_screen_option_product(){
			global $WCRL_product_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'product_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_product_screen = new WCRL_Products();
	    }

	    // Category page
	    public function WCRL_screen_option_category(){
		    global $WCRL_category_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'category_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_category_screen = new WCRL_Categories();
	    }

	    // Coupon page
	    public function WCRL_screen_option_coupon(){
		    global $WCRL_coupon_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'coupon_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_coupon_screen = new WCRL_Coupons();
	    }
	    // Payment Gatway page
	    public function WCRL_screen_option_payment_gateway(){
		    global $WCRL_payment_gateway_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'payment_gateway_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_payment_gateway_screen = new WCRL_PaymentGateway();
	    }
	    // Order Status page
	    public function WCRL_screen_option_order_status(){
		    global $WCRL_order_status_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'order_status_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_order_status_screen = new WCRL_OrdersStatus();
	    }
	    // Location page
	    public function WCRL_screen_option_location(){
		    global $WCRL_location_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'location_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_location_screen = new WCRL_Locations();
	    }
	    // Shipping page
	    public function WCRL_screen_option_shipping(){
		    global $WCRL_shipping_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'shipping_per_page'
		    );
		    add_screen_option( $option, $args );
		    $WCRL_shipping_screen = new WCRL_Shippings();
	    }

	    // Tax page
	    public function WCRL_screen_option_tax(){
		    global $WCRL_tax_screen;
		    $option = 'per_page';
		    $args = array(
			    'label' => esc_html__('Reports','wc-reports-lite'),
			    'default' => 10,
			    'option' => 'tax_per_page',
		    );
		    add_screen_option( $option, $args );
		    $WCRL_tax_screen = new WCRL_Tax();
	    }
    }
endif;