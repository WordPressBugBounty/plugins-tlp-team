<?php
/**
 * Single Popup Ajax Class.
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
 * Single Popup Ajax Class.
 */
class SinglePopup {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_tlp_md_popup_single', [ $this, 'response' ] );
		add_action( 'wp_ajax_nopriv_tlp_md_popup_single', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {
		$html  = $htmlCInfo = null;
		$error = true;

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [
				'data'  => __('Security Issue','tlp-team'),
				'error' => $error,
			] );

		}
		if ( isset( $_REQUEST['id'] ) && $post_id = absint( $_REQUEST['id'] ) ) {
			global $post;
			$post = get_post( absint( $_REQUEST['id'] ) );
			if ( Fns::isMemberViewable( $post ) ) {
				$error = false;
				setup_postdata( $post );
				$settings = get_option( rttlp_team()->options['settings'] );
				$fields   = isset( $settings['detail_page_fields'] ) ? $settings['detail_page_fields'] : [ 'name', 'designation', 'short_bio', 'email', 'web_url', 'telephone', 'mobile', 'fax', 'location', 'social' ];

				// Two-column detail popup. The builder lives in Fns so this and pro's
				// AjaxController::mdPopupSingle() render byte-identical markup.
				$html .= Fns::singlePopupMarkup( $post->ID, $fields, $settings, false );
            $html .= '<script>mdPopUpSkillAnimation();</script>';
            wp_reset_postdata();
			} else {
				$html .= '<p>' . esc_html__( 'Selected is is not for team member', 'tlp-team' ) . '</p>';
			}
		} else {
			$html .= '<p>' . esc_html__( 'No item id found', 'tlp-team' ) . '</p>';
		}
		wp_send_json(
			[
				'data'  => wp_kses_post( $html ),
				'error' => $error,
			]
		);
		die();
	}
}
