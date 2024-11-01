<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WCRL_Admin_Assets', false ) ) :
/**
 * WCRL_Admin_Assets Class.
 */
class WCRL_Admin_Assets{
	/**
	 * Hook in tabs.
	 */
	public function __construct() {
	    add_action( 'admin_enqueue_scripts', array( $this, 'WCRL_adminCssJs' ) );
	}
	/**
	 * Enqueue styles and scripts
	 */
	public function WCRL_adminCssJs(){
	    global $wp_scripts;
	    $WCRL_screen = get_current_screen();
	    $WCRL_screenId = $WCRL_screen ? $WCRL_screen->id : '';
	    $WCRL_pagesScreenId = sanitize_title( esc_html__( 'WCRL', 'wc-reports-lite' ) );
	    $WCRL_screenIds   = array(
	        'toplevel' . '_page_wcrl-overview',
		    $WCRL_pagesScreenId . '_page_wcrl-orders',
	        $WCRL_pagesScreenId . '_page_wcrl-products',
	        $WCRL_pagesScreenId . '_page_wcrl-categories',
	        $WCRL_pagesScreenId . '_page_wcrl-coupons',
	        $WCRL_pagesScreenId . '_page_wcrl-payment-gateway',
	        $WCRL_pagesScreenId . '_page_wcrl-orders-status',
	        $WCRL_pagesScreenId . '_page_wcrl-locations',
		    $WCRL_pagesScreenId . '_page_wcrl-shippings',
	        $WCRL_pagesScreenId . '_page_wcrl-tax',
	        $WCRL_pagesScreenId . '_page_wcrl-stock',
	        $WCRL_pagesScreenId . '_page_wcrl-settings',
	        $WCRL_pagesScreenId . '_page_wcrl-pro',
	    );
	    // Check if WCRL Pages Screen Ids
	    if(in_array($WCRL_screenId,$WCRL_screenIds)){
	        wp_enqueue_style( 'jquery-ui-smoothness', plugins_url('assets/css/jquery-ui-smoothness.css',dirname(__FILE__)));
	        wp_enqueue_style( 'wcrl-plugin-adminstyle', plugins_url('assets/css/admin-style.css',dirname(__FILE__)));
	        wp_enqueue_script( 'adminjs', plugins_url('assets/js/admin.js',dirname(__FILE__)));
		    wp_enqueue_script('jquery-ui-core');
	        wp_enqueue_script('common');
	        wp_enqueue_script('wp-lists');
	        wp_enqueue_script('postbox');
	    }

	    // Check if jalali date
	    if(get_locale() === WCRL_FARSILANG && in_array($WCRL_screenId,$WCRL_screenIds)){
		    wp_enqueue_style( 'wcrl-plugin-adminstyle-rtl', plugins_url('assets/css/admin-style-rtl.css',dirname(__FILE__)));
	        wp_enqueue_style( 'persianDatepicker-default', plugins_url('assets/css/persianDatepicker-default.css',dirname(__FILE__)));
	        wp_enqueue_script('persianDatepicker',plugins_url('assets/js/persianDatepicker.js',dirname(__FILE__)));
	    }else{
	        wp_enqueue_script('jquery-ui-datepicker');
	    }
	}
}
endif;