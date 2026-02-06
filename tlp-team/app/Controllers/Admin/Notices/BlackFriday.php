<?php
/**
 * Black Friday Notice Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Admin\Notices;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Black Friday Notice Class.
 */
class BlackFriday {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		$black_friday = self::get_black_friday_time();
		if ( ! $black_friday ) {
			return;
		}
		add_action( 'admin_init', [ $this, 'bf_notice' ] );
	}
    public static function get_black_friday_time(){
        $current      = time();
        return  mktime( 0, 0, 0, 11, 12, 2025 ) <= $current && $current <= mktime( 0, 0, 0, 11, 5, 2025 );
    }

	/**
	 * Black Friday Notice.
	 *
	 * @return void|string
	 */
	public function bf_notice() {
		if ( get_option( 'rtteam_ny_2025' ) != '1' ) {
			if ( ! isset( $GLOBALS['rt_team_ny_2025_notice'] ) ) {
				$GLOBALS['rt_team_ny_2025_notice'] = 'rtteam_ny_2025';
				self::notice();
			}
		}
	}

	/**
	 * Render Notice
	 *
	 * @return void
	 */
	private static function notice() {

		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery' );
			}
		);

		add_action(
			'admin_notices',
			function () {
				$plugin_name   = 'Team Pro';
				$download_link = rttlp_team()->pro_version_link();
				?>
                <style>
                    .team_page_tlp_team_get_help .rttm-black-friday {
                        margin-left: 2px;
                        margin-top: 15px;
                    }
                    .rttm-black-friday .rttm-btn-wrapper .button-primary{
                        background-color: #0022ff;
                        border-color: #0022ff;
                        transition: all 0.3s ease-in-out;
                    }
                    .rttm-black-friday .rttm-btn-wrapper .button-primary:hover{
                        background-color: #0721c9;
                        border-color: #0721c9;
                    }
                    .rttm-black-friday .rttm-btn-wrapper .button-dismiss{
                        border-color: #0022ff;
                        color: #0022ff;
                        transition: all 0.3s ease-in-out;
                    }
                    .rttm-black-friday .rttm-btn-wrapper .button-dismiss:hover{
                        background-color: #0721c9;
                        border-color: #0721c9;
                        color: #fff;
                    }
                </style>
                <div class="notice notice-info is-dismissible rttm-black-friday" data-rtteamdismissable="rtteam_ny_2025"
                     style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;background: #ECE8FF;border: 1px solid #0022ff">

                    <img alt="<?php echo esc_attr( $plugin_name ); ?>"
                         src="<?php echo esc_url( rttlp_team()->assets_url() . 'images/team-pro-gif.gif' ); ?>" width="74px"
                         height="74px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
                    <h3 style="margin:0; display:flex; align-items: center"><?php echo sprintf( '%s - Black Friday ', esc_html( $plugin_name ) ); ?>
                        <img alt="<?php echo esc_attr( $plugin_name ); ?>" src="<?php echo esc_url( rttlp_team()->assets_url() . 'images/deal.gif' ); ?>" width="40px" /> <span style="color:red;margin-left:5px"> up to  40%</span>
                    </h3>

                    <p style="margin:0 0 2px;">

                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo __( "🚀 Exciting News: <b>Team Pro’s Black Friday Sale Has Officially Begun! </b>", "tlp-team" );
                        ?>
                        Grab the plugin today and unlock unbeatable discounts before they’re gone!
                    </p>

                    <p style="margin:0;" class="rttm-btn-wrapper">
                        <a class="button button-primary" href="<?php echo esc_url( $download_link ); ?>" target="_blank">Get The Deal</a>
                        <a class="button button-dismiss" href="#">Dismiss</a>
                    </p>

                </div>
					<?php
			}
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-rtteamdismissable] .notice-dismiss, div[data-rtteamdismissable] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'rtteam_dismiss_admin_notice',
											'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rtteam-dismissible-notice' ) ); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
				<?php
			}
		);

		add_action(
			'wp_ajax_rtteam_dismiss_admin_notice',
			function () {
				check_ajax_referer( 'rtteam-dismissible-notice', 'nonce' );

				update_option( 'rtteam_ny_2025', '1' );
				wp_die();
			}
		);
	}
}
