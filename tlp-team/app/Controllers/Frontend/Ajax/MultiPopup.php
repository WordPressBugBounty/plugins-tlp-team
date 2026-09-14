<?php
/**
 * Multi Popup Ajax Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Frontend\Ajax;

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Multi Popup Ajax Class.
 */
class MultiPopup {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_tlp_multi_popup_single', [ $this, 'response' ] );
		add_action( 'wp_ajax_nopriv_tlp_multi_popup_single', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * Returns ONE member's viewer panel. The fullscreen shell around it — top bar,
	 * counter, member thumbnail strip — is owned by the runtime and survives across
	 * members, so it is deliberately not part of this payload.
	 *
	 * @return void
	 */
	public function response() {
		$html    = null;
		$success = false;
		$error   = true;

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error(
				[
					'data'  => __( 'Security Issue', 'tlp-team' ),
					'error' => $error,
				]
			);
		}

		if ( isset( $_REQUEST['id'] ) && $post_id = absint( $_REQUEST['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			global $post;
			$post = get_post( $post_id );

			if ( $post && $post->post_type == rttlp_team()->post_type ) {
				setup_postdata( $post );

				$settings = get_option( rttlp_team()->options['settings'] );
				$fields   = isset( $settings['detail_page_fields'] ) ? $settings['detail_page_fields'] : [ 'name', 'designation', 'short_bio', 'email', 'web_url', 'telephone', 'mobile', 'fax', 'location', 'social' ];

				// Built by Fns so the Elementor handler (pro AjaxController::multiPopup)
				// cannot drift from this one.
				$html   .= Fns::multiPopupMarkup( $post->ID, $fields, $settings, false );
				$success = true;
				$error   = false;

				wp_reset_postdata();
			} else {
				$html .= '<p>' . esc_html__( 'Selected is is not for team member', 'tlp-team' ) . '</p>';
			}
		} else {
			$html .= '<p>' . esc_html__( 'No item id found', 'tlp-team' ) . '</p>';
		}

		wp_send_json(
			[
				'data'    => $html,
				'error'   => $error,
				'success' => $success,
			]
		);
		die();
	}
}
