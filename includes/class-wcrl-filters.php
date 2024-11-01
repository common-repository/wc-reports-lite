<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WCRL_Filters', false ) ) :
    /**
     * WCRL_Filters Class.
     */
    class WCRL_Filters{
        /**
         * WCRL_Filters Output html
         */
        public static function WCRL_searchByOrderStatuses(){
            $wcrl_statuses = wc_get_order_statuses();
	        $selected = esc_html__('all','wc-reports-lite');
            if(isset($_GET['wcrl_orderStatuses'])){
                $selected = esc_attr($_GET['wcrl_orderStatuses']);
            }
        ?>
            <select name="wcrl_orderStatuses">
                <option value="all"><?php esc_html_e('All Order Statuses','wc-reports-lite');?></option>
		        <?php foreach ($wcrl_statuses as $key => $status){?>
                    <option value="<?php echo esc_html__($key,'wc-reports-lite');?>" <?php echo ($key == $selected)? 'selected':'';?>><?php echo esc_html__($status,'wc-reports-lite');?></option>
		        <?php }?>
            </select>
        <?php
        }
    }
endif;