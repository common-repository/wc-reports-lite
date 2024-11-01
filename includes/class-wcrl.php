<?php
defined( 'ABSPATH' ) || exit;
final class WCRL{
    private static $instance=null;
    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * WCRL constructor.
     */
    public function __construct(){
        $this->define_constants();
        $this->init_hooks();
        $this->includes();
    }

    /**
     *  WCRL constants define
     */
    private function define_constants(){
        define('WCRL_FARSILANG','fa_IR');
        define('WCRL_VERSION','1.0.0');
        define('WCRL_DIR',trailingslashit(plugin_dir_path(__FILE__)));
        define('WCRL_URL',trailingslashit(plugin_dir_url(__FILE__)));
        define('WCRL_ADMIN_PAGES',trailingslashit(WCRL_DIR.'admin-pages'));
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks(){
	    add_action( 'init',array($this,'wcrl_load_textdomain'));
    }

	/**
	 * Load plugin textdomain.
	 */
	public function wcrl_load_textdomain() {
		load_plugin_textdomain( 'wc-reports-lite', false, plugin_basename( dirname( WCRL_PLUGIN_FILE ) ) . '/languages');
	}

	/**
     * Include required core files used in admin
     */
    public function includes(){
        /**
         * Class WCRL_Autoloader.
         */
        include_once WCRL_DIR.'class-wcrl-autoloader.php';

        /**
         * Class WCRL_Admin_Menus
         */
        new WCRL_Admin_Menus();

        /**
         * Class WCRL_Admin_Assets
         */
        new WCRL_Admin_Assets();

	    /**
	     * Class WCRL_Settings
	     */
        new WCRL_Settings();
    }
}
