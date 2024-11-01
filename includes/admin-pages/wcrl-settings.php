<?php
defined( 'ABSPATH' ) || exit;

class WCRL_Settings {

	/**
	 * settings function
	 */
	public static function wcrl_perPageWidget() {
		$per_page_widget = 10;
		if ( get_option( 'wcrl_limitReportsWidgets' ) ) {
			$per_page_widget = get_option( 'wcrl_limitReportsWidgets' );
		}
		return $per_page_widget;
	}

	/**
	 * Save settings General tab
	 */
	public function wcrl_settingsFieldsGeneral() {
		// General tab
		if ( isset( $_POST['wcrl_settingsSave'] ) ) {
			/* The number of reports per widget in overview page */
			isset( $_POST['wcrl_limitReportsWidgets'] ) ? update_option( 'wcrl_limitReportsWidgets', sanitize_text_field($_POST['wcrl_limitReportsWidgets']) ) : update_option( 'wcrl_limitReportsWidgets', "" );

			// updated form ...
			?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'General Settings saved.', 'wc-reports-lite' ); ?></p>
            </div>
			<?php
		}
	}

	/**
	 * output html form
	 */
	public function wcrl_output() {
		$this->wcrl_settingsFieldsGeneral();
		?>
        <div class="wrap wcrl-settings-container">
            <h1><?php esc_html_e( 'Settings', 'wc-reports-lite' ); ?></h1>
            <form action="" method="post">
                <ul class="tabs">
                    <li class="tab-link current" data-tab="general-settings">
                        <span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'General Settings', 'wc-reports-lite' ); ?>
                    </li>
                </ul>
                <div id="general-settings" class="tab-content current">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e( 'The number of reports per widget in overview page :', 'wc-reports-lite' ); ?></label>
                            </th>
                            <td>
                                <input name="wcrl_limitReportsWidgets" type="number" max="20" value="<?php echo get_option( 'wcrl_limitReportsWidgets' ); ?>">
                                <p class="description"><?php esc_html_e( 'By default, 10 reports', 'wc-reports-lite' ); ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
					<?php submit_button( esc_html__( 'Save Settings', 'wc-reports-lite' ), 'primary', 'wcrl_settingsSave' ); ?>
                </div>
            </form>
        </div>
		<?php
	}
}