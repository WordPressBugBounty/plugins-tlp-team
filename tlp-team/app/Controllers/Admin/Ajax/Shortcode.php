<?php
/**
 * Shortcode List Ajax Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Admin\Ajax;

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Shortcode List Ajax Class.
 */
class Shortcode {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_teamShortcodeList', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {
		// Check user capabilities first
		if ( ! ( current_user_can( 'manage_options' ) || current_user_can( 'edit_pages' ) ) ) {
			wp_send_json_error( [
				'msg' => esc_html__( 'Permission denied', 'tlp-team' ),
			] );
		}

		// Verify nonce - CRITICAL FIX
		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [
				'msg' => esc_html__( 'Security check failed', 'tlp-team' ),
			] );
		}

		// If we reach here, all checks passed - proceed with the query
		$scQ = new \WP_Query(
			[
				'post_type'      => rttlp_team()->shortCodePT,
				'order_by'       => 'title',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			]
		);

		// Start output buffering to capture HTML
		ob_start();

		if ( $scQ->have_posts() ) {
			?>
            <div class='mce-container mce-form'>
                <div class='mce-container-body'>
                    <label class="mce-widget mce-label" style="padding: 20px;font-weight: bold;"
                           for="scid"><?php esc_html_e( 'Select Shortcode', 'tlp-team' ); ?></label>
                    <select name='id' id='scid' style='width: 150px;margin: 15px;'>
                        <option value=''><?php esc_html_e( 'Default', 'tlp-team' ); ?></option>
						<?php
						while ( $scQ->have_posts() ) {
							$scQ->the_post();
							?>
                            <option value='<?php echo esc_attr( get_the_ID() ); ?>'><?php echo esc_html( get_the_title() ); ?></option>
							<?php
						}
						wp_reset_postdata();
						?>
                    </select>
                </div>
            </div>
			<?php
		} else {
			?>
            <div><?php esc_html_e( 'No shortCode found.', 'tlp-team' ); ?></div>
			<?php
		}
		// Get buffered content and send success response
		$html = ob_get_clean();
		wp_send_json_success( [
			'html' => $html,
		] );
		die();
	}
}
