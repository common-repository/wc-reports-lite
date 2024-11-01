<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WCRL_Search_Date', false ) ) :
    /**
     * WCRL_Search_Date Class.
     */
    class WCRL_Search_Date{
        /**
         * wcrl_searchByDate Output html
         */
	    public static function WCRL_searchByDate(){
		    $wcrl_screen = get_current_screen();
		    $wcrl_pagesScreenId = sanitize_title( esc_html__( 'WCRL', 'wc-reports-lite' ) ).'_page_';
		    $wcrl_page = str_replace($wcrl_pagesScreenId,'',$wcrl_screen->id);
		    ?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $wcrl_page;?>" />
                <div class="wcrl_filterBox">
                    <div class="form-field">
                        <label><?php esc_html_e('Date','wc-reports-lite');?></label>
                        <select name="wcrl_Time" id="wcrl_Time" autocomplete="off">
                            <option><?php echo esc_html__('Select Date','wc-reports-lite');?></option>
                            <option value="Today" <?php if (isset($_GET['wcrl_Time'])) if (sanitize_text_field($_GET['wcrl_Time']) === 'Today') echo 'selected'?>><?php echo esc_html__('Today','wc-reports-lite');?></option>
                            <option value="Yesterday" <?php if (isset($_GET['wcrl_Time'])) if (sanitize_text_field($_GET['wcrl_Time']) === 'Yesterday') echo 'selected'?>><?php echo esc_html__('Yesterday','wc-reports-lite');?></option>
                            <option value="Last_Week" <?php if (isset($_GET['wcrl_Time'])) if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Week') echo 'selected'?>><?php echo esc_html__('Last Week','wc-reports-lite');?></option>
                            <option value="Last_Month" <?php if (isset($_GET['wcrl_Time'])) if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Month') echo 'selected'?>><?php echo esc_html__('Last Month','wc-reports-lite');?></option>
                            <option value="Last_Year" <?php if (isset($_GET['wcrl_Time'])) if (sanitize_text_field($_GET['wcrl_Time']) === 'Last_Year') echo 'selected'?>><?php echo esc_html__('Last Year','wc-reports-lite');?></option>
                        </select>
                    </div>
                    <div class="clear"></div>
                    <input type="submit" name="wcrl_searchByDate" class="button button-primary" value="<?php esc_html_e('Search','wc-reports-lite');?>">
                </div>
            </form>
		    <?php
	    }
    }
endif;